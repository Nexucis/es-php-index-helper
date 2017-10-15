<?php

namespace Nexucis\Tests\Elasticsearch\Helper\Nodowntime;

class IndexActionTest extends AbstractIndexHelperTest
{

    /**
     * @dataProvider aliasDataProvider
     */
    public function testCreateIndex($alias)
    {
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

    /**
     * @dataProvider aliasDataProvider
     */
    public function testDeleteIndex($alias)
    {
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

    /**
     * @dataProvider aliasDataProvider
     */
    public function testCopyEmptyIndex($alias)
    {
        self::$HELPER->createIndex($alias);

        $aliasDest = $alias . '2';

        $this->assertEquals(self::$HELPER::RETURN_ACKNOWLEDGE, self::$HELPER->copyIndex($alias, $aliasDest));

        $this->assertTrue(self::$HELPER->existsIndex($alias));
        $this->assertTrue(self::$HELPER->existsIndex($alias . self::$HELPER::INDEX_NAME_CONVENTION_1));
        $this->assertTrue(self::$HELPER->existsIndex($aliasDest));
        $this->assertTrue(self::$HELPER->existsIndex($aliasDest . self::$HELPER::INDEX_NAME_CONVENTION_1));
    }

    public function testCopyIndex()
    {
        $alias = 'financial';
        // create index with some contents
        $this->loadFinancialIndex($alias);

        $aliasDest = "indexcopy";
        $this->assertEquals(self::$HELPER::RETURN_ACKNOWLEDGE, self::$HELPER->copyIndex($alias, $aliasDest, true));

        $this->assertTrue(self::$HELPER->existsIndex($alias));
        $this->assertTrue(self::$HELPER->existsIndex($alias . self::$HELPER::INDEX_NAME_CONVENTION_1));
        $this->assertTrue(self::$HELPER->existsIndex($aliasDest));
        $this->assertTrue(self::$HELPER->existsIndex($aliasDest . self::$HELPER::INDEX_NAME_CONVENTION_1));
        $this->assertEquals($this->countDocuments($alias), $this->countDocuments($aliasDest));
    }

    public function testCopyIndexAsynchronusByTask()
    {
        $alias = 'financial';
        // create index with some contents
        $this->loadFinancialIndex($alias);

        $aliasDest = "indexcopy";

        $result = self::$HELPER->copyIndex($alias, $aliasDest, false, false);
        $this->assertRegExp('/\w+:\d+/i', $result);
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

    /**
     * @dataProvider aliasDataProvider
     */
    public function testReindexEmptyIndex($alias)
    {
        self::$HELPER->createIndex($alias);

        $this->assertEquals(self::$HELPER::RETURN_ACKNOWLEDGE, self::$HELPER->reindex($alias));

        $this->assertTrue(self::$HELPER->existsIndex($alias));
        $this->assertTrue(self::$HELPER->existsIndex($alias . self::$HELPER::INDEX_NAME_CONVENTION_2));
    }

    public function testReindex()
    {
        $alias = 'financial';
        // create index with some contents
        $this->loadFinancialIndex($alias);

        $this->assertEquals(self::$HELPER::RETURN_ACKNOWLEDGE, self::$HELPER->reindex($alias, true));

        $this->assertTrue(self::$HELPER->existsIndex($alias));
        $this->assertTrue(self::$HELPER->existsIndex($alias . self::$HELPER::INDEX_NAME_CONVENTION_2));
        $this->assertTrue($this->countDocuments($alias) > 0);
    }

    public function testReindexAsynchronusByTask()
    {
        $alias = 'financial';
        // create index with some contents
        $this->loadFinancialIndex($alias);

        $result = self::$HELPER->reindex($alias, false, true, false);

        $this->assertRegExp('/\w+:\d+/i', $result);
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
