<?php

namespace Nexucis\Elasticsearch\Helper\Nodowntime;

use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\InvalidArgumentException;
use Elasticsearch\Common\Exceptions\RuntimeException;
use Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexAlreadyExistException;
use Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException;
use stdClass;

/**
 * Class IndexHelper : This class can help you to manage your index with the alias management.
 * According to the official documentation https://www.elastic.co/guide/en/elasticsearch/guide/master/index-aliases.html,
 * alias management allow to use with no downtime your index.
 *
 * @category Elasticsearch Helper
 * @package  Nexucis\Elasticsearch\Helper\Nodowntime
 * @author   Augustin Husson <husson.augustin@gmail.com>
 * @license  MIT
 */
class IndexHelper implements IndexHelperInterface
{

    const INDEX_NAME_CONVENTION_1 = '_v1';
    const INDEX_NAME_CONVENTION_2 = '_v2';

    const RETURN_ACKNOWLEDGE = "ok";

    /**
     * @var Client
     */
    protected $client;

    /**
     * You can pass an alias name or an index name here.
     *
     * @param string $index [REQUIRED]
     * @return bool
     */
    public function existsIndex($index)
    {
        $params = array(
            'index' => $index,
        );

        return $this->client->indices()->exists($params);
    }


    /**
     * @param string $alias [REQUIRED]
     * @return void
     * @throws IndexAlreadyExistException
     */
    public function createIndex($alias)
    {
        $index = $alias . self::INDEX_NAME_CONVENTION_1;

        if ($this->existsIndex($index)) {
            throw new IndexAlreadyExistException($index);
        }

        $params = array(
            'index' => $index,
            'body' => array(
                'aliases' => array(
                    $alias => new stdClass()
                )),
        );

        $this->client->indices()->create($params);
    }

    /**
     * @param $index : index or alias can put here [REQUIRED]
     * @return void
     * @throws IndexNotFoundException
     */
    public function deleteIndex($index)
    {
        $params = array(
            'index' => $index
        );

        if (!$this->existsIndex($index)) {
            throw new IndexNotFoundException($index);
        }

        $this->client->indices()->delete($params);
    }

    /**
     * @param string $aliasSrc [REQUIRED]
     * @param string $aliasDest [REQUIRED]
     * @param string|bool $refresh wait until the result are visible to search
     * @param bool $waitForCompletion : According to the official documentation (https://www.elastic.co/guide/en/elasticsearch/reference/2.4/docs-reindex.html),
     * it is strongly advised to not set this parameter to false with ElasticSearch 2.4. In fact, it would be preferable to create an asynchronous process that executes this task.
     * If you set it to true, don't forget to put an alias to the new index when the corresponding task is gone.
     * @return string : the task ID if the parameter $waitForCompletion is set to false, acknowledge if not
     * @throws RuntimeException
     * @throws IndexNotFoundException
     * @throws IndexAlreadyExistException
     */
    public function copyIndex($aliasSrc, $aliasDest, $refresh = false, $waitForCompletion = true)
    {
        if (!$this->existsAlias($aliasSrc)) {
            throw new IndexNotFoundException($aliasSrc);
        }

        if ($this->existsAlias($aliasDest)) {
            throw new IndexAlreadyExistException($aliasDest);
        }

        $indexSrc = $this->findIndexByAlias($aliasSrc);
        $indexDest = $aliasDest . self::INDEX_NAME_CONVENTION_1;


        $this->copyMappingAndSetting($indexSrc, $indexDest);

        // currently, the reindex api doesn't work when there are no documents inside the index source
        // So if there are some documents to copy and if the reindex Api send an error, we throw a RuntimeException
        if (!$this->indexIsEmpty($indexSrc)) {
            $response = $this->copyDocuments($indexSrc, $indexDest, $refresh, $waitForCompletion);

            if ($waitForCompletion) {
                if (!$response) {
                    $this->deleteIndex($indexDest);
                    throw new RuntimeException('reindex failed');
                }
            } else {
                // return the task ID
                return $response;
            }
        }

        $this->putAlias($aliasDest, $indexDest);

        return self::RETURN_ACKNOWLEDGE;
    }

    /**
     * @param string $alias [REQUIRED]
     * @param string|bool $refresh wait until the result are visible to search
     * @param bool $needToCreateIndexDest
     * @param bool $waitForCompletion : According to the official documentation (https://www.elastic.co/guide/en/elasticsearch/reference/2.4/docs-reindex.html),
     * it is strongly advised to not set this parameter to false with ElasticSearch 2.4.
     * If you set it to true, don't forget to remove the old index and to switch the alias after the task is gone.
     * @return string : the task ID if the parameter $waitForCompletion is set to false, acknowledge if not
     * @throws RuntimeException
     * @throws IndexNotFoundException
     */
    public function reindex($alias, $refresh = false, $needToCreateIndexDest = true, $waitForCompletion = true)
    {
        if (!$this->existsAlias($alias)) {
            throw new IndexNotFoundException($alias);
        }

        $indexSrc = $this->findIndexByAlias($alias);
        $indexDest = $this->getIndexDest($alias, $indexSrc);


        if ($needToCreateIndexDest) { // for example, if you have updated your settings/mappings, your index_dest is already created. So you don't need to create it again
            if ($this->existsIndex($indexDest)) {
                $this->deleteIndex($indexDest);
            }

            $this->copyMappingAndSetting($indexSrc, $indexDest);
        }

        // currently, the reindex api doesn't work when there are no documents inside the index source
        // So if there are some documents to copy and if the reindex Api send an error, we throw a RuntimeException

        if (!$this->indexIsEmpty($indexSrc)) {
            $response = $this->copyDocuments($indexSrc, $indexDest, $refresh, $waitForCompletion);

            if ($waitForCompletion) {
                if (!$response) {
                    $this->deleteIndex($indexDest);
                    throw new RuntimeException('reindex failed');
                }
            } else {
                // return the task ID
                return $response;
            }
        }

        $this->switchIndex($alias, $indexSrc, $indexDest);
        $this->deleteIndex($indexSrc);

        return self::RETURN_ACKNOWLEDGE;
    }

    /**
     * This method must call when you want to add something inside the settings. Because the reindexation is a long task,
     * you should do the difference between add and delete something inside the settings. In the add task,
     * you don't need to reindex , unlike the delete task
     *
     * @param string $alias [REQUIRED]
     * @param array $settings [REQUIRED]
     * @return void
     * @throws IndexNotFoundException
     * @throws InvalidArgumentException
     */
    public function addSettings($alias, $settings)
    {
        if (!$this->existsAlias($alias)) {
            throw new IndexNotFoundException($alias);
        }

        if (!is_array($settings) || count($settings) === 0) {
            throw new InvalidArgumentException("settings are empty, you are not allowed to add an empty array as the settings.");
        }

        $indexSource = $this->findIndexByAlias($alias);

        $this->closeIndex($indexSource);
        $params = array(
            'index' => $indexSource,
            'body' => array(
                'settings' => $settings
            )
        );

        $this->client->indices()->putSettings($params);

        $this->openIndex($indexSource);
    }

    /**
     * This method must call when you want to delete something inside the settings.
     *
     * @param string $alias [REQUIRED]
     * @param array $settings [REQUIRED]
     * @param string|bool $refresh wait until the result are visible to search
     * @param bool $needReindexation : The process of reindexation can be so long, instead of calling reindex method inside this method,
     * you may want to call it in an asynchronous process.
     * But if you pass this parameters to false, don't forget to reindex. If you don't do it, you will not see your modification of the settings
     * @param bool $waitForCompletion : According to the official documentation (https://www.elastic.co/guide/en/elasticsearch/reference/2.4/docs-reindex.html),
     * it is strongly advised to not set this parameter to false with ElasticSearch 2.4.
     * If you set it to true, don't forget to remove the old index and to switch the alias after the task is gone.
     * @return string : the task ID if the parameter $waitForCompletion is set to false, acknowledge if not
     * @throws RuntimeException
     * @throws IndexNotFoundException
     */
    public function updateSettings($alias, $settings, $refresh = false, $needReindexation = true, $waitForCompletion = true)
    {
        if (!$this->existsAlias($alias)) {
            throw new IndexNotFoundException($alias);
        }

        $indexSrc = $this->findIndexByAlias($alias);
        $indexDest = $this->getIndexDest($alias, $indexSrc);

        if ($this->existsIndex($indexDest)) {
            $this->deleteIndex($indexDest);
        }

        $mapping = $this->getMappingsByIndex($indexSrc)[$indexSrc];
        $mappingSource = null;

        if (is_array($mapping) && array_key_exists('mappings', $mapping)) {
            $mappingSource = $mapping['mappings'];
        }

        $params = array(
            'index' => $indexDest,
        );

        if (is_array($settings) && count($settings) > 0) {
            $params['body'] = array(
                'settings' => $settings
            );
        }

        if (is_array($mappingSource) && (count($mappingSource) !== 0)) {
            $this->createBody($params);
            $params['body']['mappings'] = $mappingSource;
        }

        $result = $this->client->indices()->create($params);

        if ($result['acknowledged'] && $needReindexation) {
            return $this->reindex($alias, $refresh, false, $waitForCompletion);
        }

        return self::RETURN_ACKNOWLEDGE;
    }

    /**
     * This method must call whenever you want to add or delete something inside the mappings
     *
     * @param string $alias [REQUIRED]
     * @param array $mapping [REQUIRED]
     * @param string|bool $refresh wait until the result are visible to search
     * @param bool $needReindexation : The process of reindexation can be so long, instead of calling reindex method inside this method,
     * you may want to call it in an asynchronous process.
     * But if you pass this parameters to false, don't forget to reindex. If you don't do it, you will not see your modification of the mappings
     * @param bool $waitForCompletion : According to the official documentation (https://www.elastic.co/guide/en/elasticsearch/reference/2.4/docs-reindex.html),
     * it is strongly advised to not set this parameter to false with ElasticSearch 2.4.
     * If you set it to true, don't forget to remove the old index and to switch the alias after the task is gone.
     * @return string : the task ID if the parameter $waitForCompletion is set to false, acknowledge if not
     * @throws RuntimeException
     * @throws IndexNotFoundException
     */
    public function updateMappings($alias, $mapping, $refresh = false, $needReindexation = true, $waitForCompletion = true)
    {
        if (!$this->existsAlias($alias)) {
            throw new IndexNotFoundException($alias);
        }

        $indexSrc = $this->findIndexByAlias($alias);
        $indexDest = $this->getIndexDest($alias, $indexSrc);
        if ($this->existsIndex($indexDest)) {
            $this->deleteIndex($indexDest);
        }

        $settings = $this->getSettingsByIndex($indexSrc)[$indexSrc]['settings']['index'];

        $params = array(
            'index' => $indexDest,
        );

        if (is_array($mapping) && count($mapping) > 0) {
            $params['body'] = array(
                'mappings' => $mapping,
            );
        }

        $this->copySettings($params, $settings);

        $result = $this->client->indices()->create($params);

        if ($result['acknowledged'] && $needReindexation) {
            return $this->reindex($alias, $refresh, false, $waitForCompletion);
        }

        return self::RETURN_ACKNOWLEDGE;
    }

    /**
     * @return array
     */
    public function getListAlias()
    {
        $indices = $this->client->indices()->getAliases();
        $result = array();
        foreach ($indices as $index) {
            foreach ($index['aliases'] as $alias => $params_alias) {
                $result[] = $alias;
            }
        }
        return $result;
    }

    /**
     * @param string $alias [REQUIRED]
     * @return array
     * @throws IndexNotFoundException
     */
    public function getMappings($alias)
    {
        if (!$this->existsAlias($alias)) {
            throw new IndexNotFoundException($alias);
        }

        $indexSource = $this->findIndexByAlias($alias);
        $mapping = $this->getMappingsByIndex($indexSource)[$indexSource];

        if (is_array($mapping) && array_key_exists('mappings', $mapping)) {
            return $mapping['mappings'];
        }

        return array();
    }

    /**
     * @param string $alias [REQUIRED]
     * @return array
     * @throws IndexNotFoundException
     */
    public function getSettings($alias)
    {
        if (!$this->existsAlias($alias)) {
            throw new IndexNotFoundException($alias);
        }

        $indexSource = $this->findIndexByAlias($alias);
        return $this->getSettingsByIndex($indexSource)[$indexSource]['settings']['index'];
    }

    /**
     * @param string $alias [REQUIRED]
     * @param int $from the offset from the first result you want to fetch (0 by default)
     * @param int $size allows you to configure the maximum amount of hits to be returned. (10 by default)
     * @return array
     * @throws IndexNotFoundException
     */
    public function getAllDocuments($alias, $from = 0, $size = 10)
    {
        return $this->searchDocuments($alias, null, null, $from, $size);
    }

    /**
     * @param string $alias [REQUIRED]
     * @param array|null $query
     * @param string $type
     * @param int $from the offset from the first result you want to fetch (0 by default)
     * @param int $size allows you to configure the maximum amount of hits to be returned. (10 by default)
     * @return array
     * @throws IndexNotFoundException
     */
    public function searchDocuments($alias, $query, $type = null, $from = 0, $size = 10)
    {
        if (!$this->existsAlias($alias)) {
            throw new IndexNotFoundException($alias);
        }

        $params = array(
            'index' => $alias,
            'size' => $size,
            'from' => $from,
        );

        if (is_array($query)) {
            $params['body'] = array('query' => $query);
        }

        if ($type !== null) {
            $params['type'] = $type;
        }

        return $this->client->search($params);
    }

    /**
     * @param string $index [REQUIRED] If the alias is associated to an unique index, you can pass an alias rather than an index
     * @param $id [REQUIRED]
     * @param string $type [REQUIRED]
     * @param array $body [REQUIRED] : actual document to update
     * @return boolean : true if the document has been updated. Otherwise, the document has been created.
     * @throws IndexNotFoundException
     */
    public function updateDocument($index, $id, $type, $body)
    {
        if (!$this->existsIndex($index)) {
            throw new IndexNotFoundException($index);
        }
        return $this->indexDocument($index, $body, $type, $id) > 1;
    }

    /**
     * @param string $index [REQUIRED] If the alias is associated to an unique index, you can pass an alias rather than an index
     * @param $id [REQUIRED]
     * @param string $type [REQUIRED]
     * @param array $body [REQUIRED] : actual document to create
     * @return boolean : true if the document has been created.
     * @throws IndexNotFoundException
     */
    public function addDocument($index, $type, $body, $id = null)
    {
        if (!$this->existsIndex($index)) {
            throw new IndexNotFoundException($index);
        }
        return $this->indexDocument($index, $body, $type, $id) === 1;
    }

    /**
     * Remove all documents from the given index seen through its alias
     *
     * @param string $alias [REQUIRED]
     * @return void
     * @throws IndexNotFoundException
     */
    public function deleteAllDocuments($alias)
    {
        if (!$this->existsAlias($alias)) {
            throw new IndexNotFoundException($alias);
        }

        $indexSrc = $this->findIndexByAlias($alias);
        $indexDest = $this->getIndexDest($alias, $indexSrc);

        if ($this->existsIndex($indexDest)) {
            $this->deleteIndex($indexDest);
        }

        $this->copyMappingAndSetting($indexSrc, $indexDest);

        $this->switchIndex($alias, $indexSrc, $indexDest);

        $this->deleteIndex($indexSrc);
    }

    /**
     * @param $alias [REQUIRED]
     * @param $id [REQUIRED]
     * @param string $type [REQUIRED]
     * @return void
     * @throws IndexNotFoundException
     */
    public function deleteDocument($alias, $id, $type)
    {
        if (!$this->existsAlias($alias)) {
            throw new IndexNotFoundException($alias);
        }

        $params = array(
            'index' => $alias,
            'type' => $type,
            'id' => $id,
        );

        $this->client->delete($params);
    }

    /**
     * @param Client $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @param string $index
     * @param string|integer $id
     * @param array $body
     * @param string $type
     * @return mixed
     */
    protected function indexDocument($index, $body, $type, $id = null)
    {

        $params = array(
            'index' => $index,
            'type' => $type,
            'body' => $body
        );

        if ($id !== null) {
            $params['id'] = $id;
        }

        $response = $this->client->index($params);

        return $response['_version'];
    }

    /**
     * @param string $index
     */
    protected function openIndex($index)
    {
        $params = array(
            'index' => $index
        );
        $this->client->indices()->open($params);
    }

    /**
     * @param string $index
     */
    protected function closeIndex($index)
    {
        $params = array(
            'index' => $index
        );
        $this->client->indices()->close($params);
    }

    /**
     * @param string $alias
     * @return string
     */
    protected function findIndexByAlias($alias)
    {
        $params = array(
            'name' => urlencode($alias)
        );
        return array_keys($this->client->indices()->getAlias($params))[0];
    }

    /**
     * @param $index
     * @return bool : true if the index doesn't have any documents. False otherwise.
     */
    protected function indexIsEmpty($index)
    {
        return $this->countDocuments($index) == 0;
    }

    /**
     * @param $index
     * @return int
     */
    protected function countDocuments($index)
    {
        $params = array(
            'index' => $index,
        );

        return $this->client->count($params)['count'];
    }

    /**
     * @param string $alias
     * @return bool
     */
    protected function existsAlias($alias)
    {
        $params = array(
            'name' => urlencode($alias)
        );

        $response = $this->client->indices()->existsAlias($params);

        if (is_array($response) && array_key_exists('status', $response)) {
            return $response['status'] === 200;
        } elseif (is_bool($response)) {
            return $response;
        }

        return false;
    }

    /**
     * @param $indexSource
     * @param $indexDest
     */
    protected function copyMappingAndSetting($indexSource, $indexDest)
    {
        $params = array(
            'index' => $indexDest,
        );

        $mapping = $this->getMappingsByIndex($indexSource)[$indexSource];
        $mappingSource = null;

        if (is_array($mapping) && array_key_exists('mappings', $mapping)) {
            $mappingSource = $mapping['mappings'];
        }

        $settingSource = $this->getSettingsByIndex($indexSource)[$indexSource]['settings']['index'];

        if (is_array($mappingSource) && (count($mappingSource) !== 0)) {
            $this->createBody($params);
            $params['body']['mappings'] = $mappingSource;
        }

        $this->copySettings($params, $settingSource);


        $this->client->indices()->create($params);
    }

    protected function copySettings(&$params, $settings)
    {
        $numberOfShards = $settings['number_of_shards'];
        $numberOfReplicas = $settings['number_of_replicas'];

        if ($numberOfShards !== null) {
            $this->createBody($params);

            $params['body']['settings'] = array(
                'number_of_shards' => $numberOfShards
            );
        }

        if ($numberOfReplicas !== null) {
            $this->createBody($params);

            if (!array_key_exists('settings', $params['body'])) {
                $params['body']['settings'] = array();
            }

            $params['body']['settings']['number_of_replicas'] = $numberOfReplicas;
        }

        $analysisSource = null;

        if (array_key_exists('analysis', $settings)) {
            $analysisSource = $settings['analysis'];
        }

        if (($analysisSource !== null) && (count($analysisSource) !== 0)) {
            $this->createBody($params);

            if (!array_key_exists('settings', $params['body'])) {
                $params['body']['settings'] = array();
            }

            $params['body']['settings']['analysis'] = $analysisSource;
        }
    }

    private function createBody(&$params)
    {
        if (!array_key_exists('body', $params)) {
            $params['body'] = array();
        }
    }

    /**
     * @param string $indexSrc
     * @param string $indexDest
     * @param string|bool $refresh wait until the result are visible to search
     * @param bool $waitForCompletion
     * @return boolean | string
     */
    protected function copyDocuments($indexSrc, $indexDest, $refresh = false, $waitForCompletion = true)
    {
        $params = array(
            'body' => array(
                'source' => array(
                    'index' => $indexSrc
                ),
                'dest' => array(
                    'index' => $indexDest
                )
            ),
            'wait_for_completion' => $waitForCompletion,
            'refresh' => $refresh
        );

        $response = $this->client->reindex($params);

        if ($waitForCompletion) {
            return count($response['failures']) === 0;
        }
        // return the task ID
        return $response['task'];
    }

    /**
     * @param string $index
     * @return array
     */
    protected function getSettingsByIndex($index)
    {
        $params = array(
            'index' => $index
        );
        return $this->client->indices()->getSettings($params);
    }

    /**
     * @param string $index
     * @return array
     */
    protected function getMappingsByIndex($index)
    {
        $params = array(
            'index' => $index
        );
        return $this->client->indices()->getMapping($params);
    }

    /**
     * @param string $alias
     * @param string $indexSrc
     * @return string
     */
    protected function getIndexDest($alias, $indexSrc)
    {
        if ($alias . self::INDEX_NAME_CONVENTION_1 === $indexSrc) {
            return $alias . self::INDEX_NAME_CONVENTION_2;
        } else {
            return $alias . self::INDEX_NAME_CONVENTION_1;
        }
    }

    /**
     * @param string $alias
     * @param string $indexSrc
     * @param string $indexDest
     */
    protected function switchIndex($alias, $indexSrc, $indexDest)
    {

        $params = array(
            'body' => array(
                'actions' => array(
                    0 => array(
                        'remove' => array(
                            'index' => $indexSrc,
                            'alias' => $alias),
                    ),
                    1 => array(
                        'add' => array(
                            'index' => $indexDest,
                            'alias' => $alias),
                    )
                ),
            ),
        );

        $this->client->indices()->updateAliases($params);
    }

    /**
     * @param string $alias
     * @param string $index
     */
    protected function putAlias($alias, $index)
    {
        $params = array(
            'index' => $index,
            'name' => urlencode($alias)
        );

        $this->client->indices()->putAlias($params);
    }
}
