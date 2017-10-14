<?php


namespace Nexucis\Elasticsearch\Helper\Nodowntime;

use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\InvalidArgumentException;
use Elasticsearch\Common\Exceptions\RuntimeException;
use Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexAlreadyExistException;
use Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException;

/**
 * Class IndexHelperInterface
 *
 * @category Elasticsearch Helper
 * @package  Nexucis\Elasticsearch\Helper\Nodowntime
 * @author   Augustin Husson <husson.augustin@gmail.com>
 * @license  MIT
 */
interface IndexHelperInterface
{

    /**
     * You can pass an alias name or an index name here.
     *
     * @param string $index [REQUIRED]
     * @return bool
     */
    public function existsIndex($index);

    /**
     * @param string $alias [REQUIRED]
     * @return void
     * @throws IndexAlreadyExistException
     */
    public function createIndex($alias);

    /**
     * @param $index : index or alias can put here [REQUIRED]
     * @return void
     * @throws IndexNotFoundException
     */
    public function deleteIndex($index);

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
    public function copyIndex($aliasSrc, $aliasDest, $refresh = false, $waitForCompletion = true);

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
    public function reindex($alias, $refresh = false, $needToCreateIndexDest = true, $waitForCompletion = true);

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
    public function addSettings($alias, $settings);

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
    public function updateSettings($alias, $settings, $refresh = false, $needReindexation = true, $waitForCompletion = true);

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
    public function updateMappings($alias, $mapping, $refresh = false, $needReindexation = true, $waitForCompletion = true);

    /**
     * @return array
     */
    public function getListAlias();

    /**
     * @param string $alias [REQUIRED]
     * @return array
     */
    public function getMappings($alias);

    /**
     * @param string $alias [REQUIRED]
     * @return array
     */
    public function getSettings($alias);

    /**
     * @param string $alias [REQUIRED]
     * @param int $from the offset from the first result you want to fetch (0 by default)
     * @param int $size allows you to configure the maximum amount of hits to be returned. (10 by default)
     * @return array
     */
    public function getAllDocuments($alias, $from = 0, $size = 10);

    /**
     * @param string $alias [REQUIRED]
     * @param array $query [REQUIRED]
     * @param null|string $type
     * @param int $from the offset from the first result you want to fetch (0 by default)
     * @param int $size allows you to configure the maximum amount of hits to be returned. (10 by default)
     * @return array
     */
    public function searchDocuments($alias, $query, $type = null, $from = 0, $size = 10);

    /**
     * @param string $index [REQUIRED] If the alias is associated to an unique index, you can pass an alias rather than an index
     * @param $id [REQUIRED]
     * @param string $type [REQUIRED]
     * @param array $body [REQUIRED] : actual document to update
     * @return boolean : true if the document has been updated. Otherwise, the document has been created.
     * @throws IndexNotFoundException
     */
    public function updateDocument($index, $id, $type, $body);

    /**
     * @param string $index [REQUIRED] If the alias is associated to an unique index, you can pass an alias rather than an index
     * @param $id [REQUIRED]
     * @param string $type [REQUIRED]
     * @param array $body [REQUIRED] : actual document to create
     * @return boolean : true if the document has been created.
     * @throws IndexNotFoundException
     */
    public function addDocument($index, $id, $type, $body);

    /**
     * Remove all documents from the given index seen through its alias
     *
     * @param string $alias [REQUIRED]
     * @return void
     * @throws IndexNotFoundException
     */
    public function deleteAllDocuments($alias);

    /**
     * @param $alias [REQUIRED]
     * @param $id [REQUIRED]
     * @param string $type [REQUIRED]
     * @return bool
     * @throws IndexNotFoundException
     */
    public function deleteDocument($alias, $id, $type);

    /**
     * @param Client $client
     */
    public function setClient($client);

}