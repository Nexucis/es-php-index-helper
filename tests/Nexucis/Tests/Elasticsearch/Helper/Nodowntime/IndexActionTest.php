<?php

namespace Nexucis\Tests\Elasticsearch\Helper\Nodowntime;

class IndexActionTest extends AbstractIndexHelperTest
{

    /**
     * @dataProvider aliasDataProvider
     */
    public function testCreateIndex(string $alias)
    {
        $this->helper->createIndexByAlias($alias);
        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_1));
    }

    /**
     * @@expectedException \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexAlreadyExistException
     */
    public function testCreateIndexAlreadyExistsException()
    {
        $alias = 'myindextest';
        $this->helper->createIndexByAlias($alias);
        $this->helper->createIndexByAlias($alias);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testDeleteIndex(string $alias)
    {
        $this->helper->createIndexByAlias($alias);
        $this->helper->deleteIndexByAlias($alias);

        $this->assertFalse($this->helper->existsIndex($alias));
        $this->assertFalse($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_1));
    }

    /**
     * @expectedException \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function testDeleteIndexNotFoundException()
    {
        $alias = 'myindextest';
        $this->helper->deleteIndexByAlias($alias);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testCopyEmptyIndex(string $alias)
    {
        $this->helper->createIndexByAlias($alias);

        $aliasDest = $alias . '2';

        $this->assertEquals($this->helper::RETURN_ACKNOWLEDGE, $this->helper->copyIndex($alias, $aliasDest));

        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_1));
        $this->assertTrue($this->helper->existsIndex($aliasDest));
        $this->assertTrue($this->helper->existsIndex($aliasDest . $this->helper::INDEX_NAME_CONVENTION_1));
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testCopyIndex(string $alias)
    {
        // create index with some contents
        $this->loadFinancialIndex($alias);

        $aliasDest = "indexcopy";
        $this->assertEquals($this->helper::RETURN_ACKNOWLEDGE, $this->helper->copyIndex($alias, $aliasDest, true));

        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_1));
        $this->assertTrue($this->helper->existsIndex($aliasDest));
        $this->assertTrue($this->helper->existsIndex($aliasDest . $this->helper::INDEX_NAME_CONVENTION_1));
        $this->assertEquals($this->countDocuments($alias), $this->countDocuments($aliasDest));
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testCopyIndexAsynchronusByTask(string $alias)
    {
        // create index with some contents
        $this->loadFinancialIndex($alias);

        $aliasDest = "indexcopy";

        $result = $this->helper->copyIndex($alias, $aliasDest, false, false);
        $this->assertRegExp('/\w+:\d+/i', $result);
    }

    /**
     * @expectedException \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function testCopyIndexNotFoundException()
    {
        $aliasSrc = 'myindextest';
        $this->helper->copyIndex($aliasSrc, $aliasSrc);
    }

    /**
     * @@expectedException \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexAlreadyExistException
     */
    public function testCopyIndexAlreadyExistsException()
    {
        $aliasSrc = 'myindextest';
        $this->helper->createIndexByAlias($aliasSrc);

        $this->helper->copyIndex($aliasSrc, $aliasSrc);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testReindexEmptyIndex(string $alias)
    {
        $this->helper->createIndexByAlias($alias);

        $this->assertEquals($this->helper::RETURN_ACKNOWLEDGE, $this->helper->reindex($alias));

        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_2));
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testReindex(string $alias)
    {
        // create index with some contents
        $this->loadFinancialIndex($alias);

        $this->assertEquals($this->helper::RETURN_ACKNOWLEDGE, $this->helper->reindex($alias, true));

        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_2));
        $this->assertTrue($this->countDocuments($alias) > 0);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testReindexWithIndexAlreadyExists(string $alias)
    {
        // create index with some contents
        $this->loadFinancialIndex($alias);

        // create index 2 in order to check if it will be deleted by the reindex process
        $this->createIndex2($alias);

        $this->assertEquals($this->helper::RETURN_ACKNOWLEDGE, $this->helper->reindex($alias, true));

        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_2));
        $this->assertTrue($this->countDocuments($alias) > 0);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testReindexAsynchronusByTask(string $alias)
    {
        // create index with some contents
        $this->loadFinancialIndex($alias);

        $result = $this->helper->reindex($alias, false, true, false);

        $this->assertRegExp('/\w+:\d+/i', $result);
    }

    /**
     * @expectedException \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function testReindexIndexNotFoundException()
    {
        $aliasSrc = 'myindextest';

        $this->helper->reindex($aliasSrc);
    }
}
