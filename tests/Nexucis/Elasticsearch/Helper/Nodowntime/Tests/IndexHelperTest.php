<?php

namespace Nexucis\Elasticsearch\Helper\Nodowntime\Tests;

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
     * initialize elasticsearch client and index Helper
     */
    public static function setUpBeforeClass()
    {
        $client = ClientBuilder::create()->setHosts([$_SERVER['ES_TEST_HOST']])->build();
        self::$HELPER = new IndexHelper();
        self::$HELPER->setClient($client);
    }

    public function testCreateIndex()
    {
        self::$HELPER->createIndex('myindextest');
    }

}
