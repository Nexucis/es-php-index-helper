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
    protected $helper;

    /**
     * @var $client \Elasticsearch\Client
     */
    protected $client;

    protected static $documents;

    /**
     * initialize static data
     */
    public static function setUpBeforeClass()
    {
        // load static data
        self::$documents = json_decode(file_get_contents('http://data.consumerfinance.gov/api/views.json'));
        parent::setUpBeforeClass();
    }

    /**
     * initialize elasticsearch client and index Helper
     */
    public function setUp()
    {
        $client = ClientBuilder::create()->setHosts([$_SERVER['ES_TEST_HOST']])->build();
        $this->helper = new IndexHelper($client);
        // in order to cover the getter
        $this->client = $this->helper->getClient();
        parent::setUp();
    }

    public function tearDown()
    {
        // remove all previously indices created by test or by the before setup
        $param = [
            'index' => '_all'
        ];
        $this->client->indices()->delete($param);
        parent::tearDown();
    }

    public function aliasDataProvider()
    {
        return [
            'latin-char' => ['myindextest'],
            'utf-8-char' => ['⿇⽸⾽']
        ];
    }

    public function aliasDataProviderWithTypeName()
    {
        return [
            'latin-char-with-type' => [
                'myindextest',
                true
            ],
            'latin-char-without-type' => [
                'myindextest',
                false
            ],
            'utf-8-char-with-type' => [
                '⿇⽸⾽',
                true
            ],
            'utf-8-char-without-type' => [
                '⿇⽸⾽',
                false
            ],
        ];
    }

    protected function createIndex2($alias)
    {
        $index = $alias . IndexHelper::INDEX_NAME_CONVENTION_2;

        $params = array(
            'index' => $index
        );
        $this->client->indices()->create($params);
    }

    protected function loadFinancialIndex($alias)
    {
        $this->helper->createIndexByAlias($alias);

        $this->addBulkDocuments($this->jsonArrayToBulkArray(self::$documents, $alias));
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

    private function addBulkDocuments($params)
    {
        $this->client->bulk($params);
    }

    private function jsonArrayToBulkArray($documents, $index)
    {
        $params = array();
        foreach ($documents as $document) {
            $params['body'][] = [
                'index' => [
                    '_index' => $index,
                ]
            ];
            $params['body'][] = $document;
        }
        // wait until the result are visible to search
        $params['refresh'] = true;
        return $params;
    }
}
