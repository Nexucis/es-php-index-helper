<?php

namespace Nexucis\Elasticsearch\Helper\Nodowntime\Tests;

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
     * @var $client Client
     */
    protected static $client;

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

    public function aliasDataProvider()
    {
        return [
            'latin-char' => ['myindextest'],
            'utf-8-char' => ['⿇⽸⾽']
        ];
    }

}