<?php

namespace Nexucis\Elasticsearch\Helper\Nodowntime\Tests;

class IndexActionTest extends AbstractIndexHelperTest
{

    public function testCreateIndex()
    {
        $alias = 'myindextest';
        self::$HELPER->createIndex($alias);
        $this->assertTrue(self::$HELPER->existsIndex($alias));
        $this->assertTrue(self::$HELPER->existsIndex($alias . self::$HELPER::INDEX_NAME_CONVENTION_1));
    }

    public function testCreateIndexUTF8()
    {
        $alias = '⿇⽸⾽';
        self::$HELPER->createIndex($alias);
        $this->assertTrue(self::$HELPER->existsIndex($alias));
        $this->assertTrue(self::$HELPER->existsIndex($alias . self::$HELPER::INDEX_NAME_CONVENTION_1));
        $this->assertEquals([$alias], self::$HELPER->getListAlias());
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

    public function testReindexEmptyIndex()
    {
        $aliasSrc = 'myindextest';
        self::$HELPER->createIndex($aliasSrc);

        self::$HELPER->reindex($aliasSrc);

        $this->assertTrue(self::$HELPER->existsIndex($aliasSrc));
        $this->assertTrue(self::$HELPER->existsIndex($aliasSrc . self::$HELPER::INDEX_NAME_CONVENTION_2));
    }

    /**
     * @expectedException \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function testReindexIndexNotFoundException()
    {
        $aliasSrc = 'myindextest';

        self::$HELPER->reindex($aliasSrc);
    }

}
