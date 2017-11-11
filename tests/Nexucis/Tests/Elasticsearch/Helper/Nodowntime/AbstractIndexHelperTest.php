<?php

namespace Nexucis\Tests\Elasticsearch\Helper\Nodowntime;

use Elasticsearch\ClientBuilder;
use Nexucis\Elasticsearch\Helper\Nodowntime\IndexHelper;
use PHPUnit\Framework\TestCase;

abstract class AbstractIndexHelperTest extends TestCase
{

    /**
     * @var $helper IndexHelper
     */
    protected static $HELPER;

    /**
     * @var $client \Elasticsearch\Client
     */
    protected static $client;

    protected static $documents;

    /**
     * initialize elasticsearch client and index Helper
     */
    public static function setUpBeforeClass()
    {
        self::$client = ClientBuilder::create()->setHosts([$_SERVER['ES_TEST_HOST']])->build();
        self::$HELPER = new IndexHelper(self::$client);

        // load static data
        self::$documents = json_decode(file_get_contents('http://data.consumerfinance.gov/api/views.json'));
    }

    public function tearDown()
    {
        // remove all previously indices created by test or by the before setup
        $param = [
            'index' => '_all'
        ];
        self::$client->indices()->delete($param);
    }

    public function aliasDataProvider()
    {
        return [
            'latin-char' => ['myindextest'],
            'utf-8-char' => ['⿇⽸⾽']
        ];
    }

    protected function loadFinancialIndex($alias, $type = 'complains')
    {
        self::$HELPER->createIndex($alias);

        $this->addBulkDocuments($this->jsonArrayToBulkArray(self::$documents, $alias, $type));
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
        return self::$client->count($params)['count'];
    }

    private function addBulkDocuments($params)
    {
        self::$client->bulk($params);
    }

    private function jsonArrayToBulkArray($documents, $index, $type)
    {
        $params = array();
        foreach ($documents as $document) {
            $params['body'][] = [
                'index' => [
                    '_index' => $index,
                    '_type' => $type,
                ]
            ];
            $params['body'][] = $document;
        }
        // wait until the result are visible to search
        $params['refresh'] = true;
        return $params;
    }
}
