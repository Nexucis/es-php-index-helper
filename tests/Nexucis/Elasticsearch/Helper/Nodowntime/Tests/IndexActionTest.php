<?php

namespace Nexucis\Elasticsearch\Helper\Nodowntime\Tests;

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

        self::$HELPER->copyIndex($alias, $aliasDest);

        $this->assertTrue(self::$HELPER->existsIndex($alias));
        $this->assertTrue(self::$HELPER->existsIndex($alias . self::$HELPER::INDEX_NAME_CONVENTION_1));
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

    /**
     * @dataProvider aliasDataProvider
     */
    public function testReindexEmptyIndex($alias)
    {
        self::$HELPER->createIndex($alias);

        self::$HELPER->reindex($alias);

        $this->assertTrue(self::$HELPER->existsIndex($alias));
        $this->assertTrue(self::$HELPER->existsIndex($alias . self::$HELPER::INDEX_NAME_CONVENTION_2));
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
