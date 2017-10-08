<?php

namespace Nexucis\Elasticsearch\Helper\Nodowntime\Tests;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Nexucis\Elasticsearch\Helper\Nodowntime\IndexHelper;
use PHPUnit\Framework\TestCase;

class IndexHelperTest extends TestCase
{
    /**
     * @var $helper IndexHelper
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
        $this->assertTrue(self::$HELPER->existsIndex($alias . self::$HELPER::INDEX_NAME_CONVENTION_1));
    }

    /**
     * @@expectedException \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexAlreadyExistException
     */
    public function testCreateIndexAlreadyExistsException()
    {
        $alias = 'myindextest';
        self::$HELPER->createIndex($alias);
        self::$HELPER->createIndex($alias);
    }

    public function testDeleteIndex()
    {
        $alias = 'myindextest';
        self::$HELPER->createIndex($alias);
        self::$HELPER->deleteIndex($alias);

        $this->assertFalse(self::$HELPER->existsIndex($alias));
        $this->assertFalse(self::$HELPER->existsIndex($alias . self::$HELPER::INDEX_NAME_CONVENTION_1));
    }

    /**
     * @expectedException \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function testDeleteIndexNotFoundException()
    {
        $alias = 'myindextest';
        self::$HELPER->deleteIndex($alias);
    }

    public function testCopyEmptyIndex()
    {
        $aliasSrc = 'myindextest';
        self::$HELPER->createIndex($aliasSrc);

        $aliasDest = 'myindextest2';

        self::$HELPER->copyIndex($aliasSrc, $aliasDest);

        $this->assertTrue(self::$HELPER->existsIndex($aliasSrc));
        $this->assertTrue(self::$HELPER->existsIndex($aliasSrc . self::$HELPER::INDEX_NAME_CONVENTION_1));
        $this->assertTrue(self::$HELPER->existsIndex($aliasDest));
        $this->assertTrue(self::$HELPER->existsIndex($aliasDest . self::$HELPER::INDEX_NAME_CONVENTION_1));
    }

    /**
     * @expectedException \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function testCopyIndexNotFoundException()
    {
        $aliasSrc = 'myindextest';
        self::$HELPER->copyIndex($aliasSrc, $aliasSrc);
    }

    /**
     * @@expectedException \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexAlreadyExistException
     */
    public function testCopyIndexAlreadyExistsException()
    {
        $aliasSrc = 'myindextest';
        self::$HELPER->createIndex($aliasSrc);

        self::$HELPER->copyIndex($aliasSrc, $aliasSrc);
    }

}
