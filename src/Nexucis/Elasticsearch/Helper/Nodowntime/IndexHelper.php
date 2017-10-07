<?php

namespace Nexucis\Elasticsearch\Helper\Nodowntime;


use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\BadMethodCallException;
use Elasticsearch\Common\Exceptions\RuntimeException;
use Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException;

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
    /**
     * @var Client
     */
    protected $client;

    protected static $INDEX_NAME_CONVENTION_1 = '_v1';
    protected static $INDEX_NAME_CONVENTION_2 = '_v2';

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
     * @throws BadMethodCallException
     */
    public function createIndex($alias)
    {
        $index = $alias . self::$INDEX_NAME_CONVENTION_1;

        if ($this->existsIndex($index)) {
            throw new BadMethodCallException('$index ' . $index . ' already exists. Cannot be created again');
        }

        $params = array(
            'index' => $index,
            'body' => array(
                'aliases' => array(
                    $alias => json_decode('{}')
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
            throw new IndexNotFoundException('$index ' . $index . 'not found');
        }

        $this->client->indices()->delete($params);
    }

    /**
     * @param string $aliasSrc [REQUIRED]
     * @param string $aliasDest [REQUIRED]
     * @param bool $waitForCompletion : According to the official documentation (https://www.elastic.co/guide/en/elasticsearch/reference/2.4/docs-reindex.html),
     * it is strongly advised to not set this parameter to false with ElasticSearch 2.4. In fact, it would be preferable to create an asynchronous process that executes this task.
     * If you set it to true, don't forget to put an alias to the new index when the corresponding task is gone.
     * @return string : the task ID if the parameter $waitForCompletion is set to false, acknowledge if not
     * @throws RuntimeException
     * @throws IndexNotFoundException
     * @throws BadMethodCallException
     */
    public function copyIndex($aliasSrc, $aliasDest, $waitForCompletion = true)
    {
        if (!$this->existsAlias($aliasSrc)) {
            throw new IndexNotFoundException('$index ' . $aliasSrc . 'not found');
        }

        if ($this->existsAlias($aliasDest)) {
            throw new BadMethodCallException('$index ' . $aliasDest . ' must not exist');
        }

        $indexSrc = $this->findIndexByAlias($aliasSrc);
        $indexDest = $aliasDest . self::$INDEX_NAME_CONVENTION_1;


        $this->copyMappingAndSetting($indexSrc, $indexDest);

        // currently, the reindex api doesn't work when there are no documents inside the index source
        // So if there are some documents to copy and if the reindex Api send an error, we throw a RuntimeException
        if (!$this->indexIsEmpty($indexSrc)) {
            $response = $this->copyDocuments($indexSrc, $indexDest, $waitForCompletion);

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

        return "ok";
    }

    /**
     * @param string $alias [REQUIRED]
     * @param bool $needToCreateIndexDest
     * @param bool $waitForCompletion : According to the official documentation (https://www.elastic.co/guide/en/elasticsearch/reference/2.4/docs-reindex.html),
     * it is strongly advised to not set this parameter to false with ElasticSearch 2.4.
     * If you set it to true, don't forget to remove the old index and to switch the alias after the task is gone.
     * @return string : the task ID if the parameter $waitForCompletion is set to false, acknowledge if not
     * @throws RuntimeException
     * @throws IndexNotFoundException
     */
    public function reindex($alias, $needToCreateIndexDest = true, $waitForCompletion = true)
    {
        if (!$this->existsAlias($alias)) {
            throw new IndexNotFoundException('$index ' . $alias . ' not found');
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
            $response = $this->copyDocuments($indexSrc, $indexDest, $waitForCompletion);

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

        return "ok";
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
     */
    public function addSettings($alias, $settings)
    {
        if (!$this->existsAlias($alias)) {
            throw new IndexNotFoundException('$index ' . $alias . ' not found');
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
    public function updateSettings($alias, $settings, $needReindexation = true, $waitForCompletion = true)
    {
        if (!$this->existsAlias($alias)) {
            throw new IndexNotFoundException('$index ' . $alias . ' not found');
        }

        $indexSrc = $this->findIndexByAlias($alias);
        $indexDest = $this->getIndexDest($alias, $indexSrc);

        if ($this->existsIndex($indexDest)) {
            $this->deleteIndex($indexDest);
        }

        $mapping = $this->getMappingByIndex($indexSrc)[$indexSrc];
        $mappingSource = null;

        if ($mapping && is_array($mapping) && array_key_exists('mappings', $mapping)) {
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

        if (($mappingSource !== null) && is_array($mappingSource) && (count($mappingSource) !== 0)) {
            $this->createBody($params);
            $params['body']['mappings'] = $mappingSource;
        }

        $result = $this->client->indices()->create($params);

        if ($result['acknowledged'] && $needReindexation) {
            return $this->reindex($alias, false, $waitForCompletion);
        }

        return "ok";
    }

    /**
     * @param string $alias [REQUIRED]
     * @param array $mapping [REQUIRED]
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
    public function updateMapping($alias, $mapping, $needReindexation = true, $waitForCompletion = true)
    {
        if (!$this->existsAlias($alias)) {
            throw new IndexNotFoundException('$index ' . $alias . ' not found');
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

        if (count($mapping) > 0) {
            $params['body'] = array(
                'mappings' => $mapping,
            );
        }

        $this->copySettings($params, $settings);

        $result = $this->client->indices()->create($params);

        if ($result['acknowledged'] && $needReindexation) {
            return $this->reindex($alias, false, $waitForCompletion);
        }

        return "ok";
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
    public function getMapping($alias)
    {
        if (!$this->existsAlias($alias)) {
            throw new IndexNotFoundException('$index ' . $alias . ' not found');
        }

        $indexSource = $this->findIndexByAlias($alias);
        return $this->getMappingByIndex($indexSource)[$indexSource]['mappings'];
    }

    /**
     * @param string $alias [REQUIRED]
     * @return array
     * @throws IndexNotFoundException
     */
    public function getSetting($alias)
    {
        if (!$this->existsAlias($alias)) {
            throw new IndexNotFoundException('$index ' . $alias . ' not found');
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
     * @param array $query [REQUIRED]
     * @param null|string $type
     * @param int $from the offset from the first result you want to fetch (0 by default)
     * @param int $size allows you to configure the maximum amount of hits to be returned. (10 by default)
     * @return array
     * @throws IndexNotFoundException
     */
    public function searchDocuments($alias, $query, $type = null, $from = 0, $size = 10)
    {
        if (!$this->existsAlias($alias)) {
            throw new IndexNotFoundException('$index ' . $alias . ' not found');
        }

        $params = array(
            'index' => $alias,
            'size' => $size,
            'from' => $from,
        );

        if ($query != null && is_array($query)) {
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
            throw new IndexNotFoundException('$index ' . $index . ' not found');
        }
        return $this->indexDocument($index, $id, $body, $type) > 1;
    }

    /**
     * @param string $index [REQUIRED] If the alias is associated to an unique index, you can pass an alias rather than an index
     * @param $id [REQUIRED]
     * @param string $type [REQUIRED]
     * @param array $body [REQUIRED] : actual document to create
     * @return boolean : true if the document has been created.
     * @throws IndexNotFoundException
     */
    public function addDocument($index, $id, $type, $body)
    {
        if (!$this->existsIndex($index)) {
            throw new IndexNotFoundException('$index ' . $index . ' not found');
        }
        return $this->indexDocument($index, $id, $body, $type) === 1;
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
            throw new IndexNotFoundException('$index ' . $alias . ' not found');
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
     * @return boolean
     * @throws IndexNotFoundException
     */
    public function deleteDocument($alias, $id, $type)
    {
        if (!$this->existsAlias($alias)) {
            throw new IndexNotFoundException('$index ' . $alias . ' not found');
        }

        $params = array(
            'index' => $alias,
            'type' => $type,
            'id' => $id,
        );

        $response = $this->client->delete($params);
        return $response['found'] > 0;
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
    protected function indexDocument($index, $id, $body, $type)
    {

        $params = array(
            'index' => $index,
            'type' => $type,
            'id' => $id,
            'body' => $body
        );

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
            'name' => $alias
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
            'name' => $alias
        );

        return $this->client->indices()->existsAlias($params);
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

        $mapping = $this->getMappingByIndex($indexSource)[$indexSource];
        $mappingSource = null;

        if ($mapping && is_array($mapping) && array_key_exists('mappings', $mapping)) {
            $mappingSource = $mapping['mappings'];
        }

        $settingSource = $this->getSettingsByIndex($indexSource)[$indexSource]['settings']['index'];

        if (($mappingSource !== null) && is_array($mappingSource) && (count($mappingSource) !== 0)) {
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

        $analysisSource = $settings['analysis'];

        if ($numberOfShards !== null) {
            $this->createBody($params);

            $params['body']['settings'] = array(
                'number_of_shards' => $numberOfShards
            );
        }

        if ($numberOfReplicas !== null) {
            $this->createBody($params);

            if ($params['body']['settings'] === null) {
                $params['body']['settings'] = array();
            }

            $params['body']['settings']['number_of_replicas'] = $numberOfReplicas;
        }

        if (($analysisSource !== null) && (count($analysisSource) !== 0)) {

            $this->createBody($params);

            if ($params['body']['settings'] === null) {
                $params['body']['settings'] = array();
            }

            $params['body']['settings']['analysis'] = $analysisSource;
        }
    }

    private function createBody(&$params)
    {
        if ($params['body'] === null) {
            $params['body'] = array();
        }
    }

    /**
     * @param string $indexSrc
     * @param string $indexDest
     * @param bool $waitForCompletion
     * @return boolean | string
     */
    protected function copyDocuments($indexSrc, $indexDest, $waitForCompletion = true)
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
            'wait_for_completion' => $waitForCompletion
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
    protected function getMappingByIndex($index)
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
        if ($alias . self::$INDEX_NAME_CONVENTION_1 === $indexSrc) {
            return $alias . self::$INDEX_NAME_CONVENTION_2;
        } else {
            return $alias . self::$INDEX_NAME_CONVENTION_1;
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
            'name' => $alias
        );

        $this->client->indices()->putAlias($params);
    }
}