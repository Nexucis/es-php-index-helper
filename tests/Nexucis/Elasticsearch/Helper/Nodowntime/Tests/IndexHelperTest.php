<?php

namespace Nexucis\Elasticsearch\Helper\Nodowntime\Tests;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Nexucis\Elasticsearch\Helper\Nodowntime\IndexHelper;
use Nexucis\Elasticsearch\Helper\Nodowntime\IndexHelperInterface;
use PHPUnit\Framework\TestCase;

class IndexHelperTest extends TestCase
{
    /**
     * @var $helper IndexHelperInterface
     */
    private static $HELPER;

    /**
     * @var $client Client
     */
    private static $client;

    /**
     * initialize elasticsearch client and index Helper
     */
    public static function setUpBeforeClass()
    {
        self::$client = ClientBuilder::create()->setHosts([$_SERVER['ES_TEST_HOST']])->build();
        self::$HELPER = new IndexHelper();
        self::$HELPER->setClient(self::$client);
    }

    public function tearDown()
    {
        // remove all previously indices created by test or by the before setup
        $param = [
            'index' => '_all'
        ];
        self::$client->indices()->delete($param);
    }

    public function testCreateIndex()
    {
        $alias = 'myindextest';
        self::$HELPER->createIndex($alias);
        $this->assertTrue(self::$HELPER->existsIndex($alias));
        $this->assertTrue(self::$HELPER->existsIndex($alias . '_v1'));
    }

}
