<?php

namespace Nexucis\Elasticsearch\Helper\Nodowntime\Tests;

class DocumentActionTest extends AbstractIndexHelperTest
{

    /**
     * @expectedException  \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function testDeleteAllDocumentsIndexNotFound()
    {
        $alias = 'myindex';
        self::$HELPER->deleteAllDocuments($alias);
    }

    public function testDeleteAllDocuments()
    {
        $alias = 'financial';
        $this->loadFinancialIndex($alias);

        $mappings = self::$HELPER->getMappings($alias);

        $this->assertTrue($this->countDocuments($alias) > 0);

        self::$HELPER->deleteAllDocuments($alias);

        $this->assertTrue($this->countDocuments($alias) == 0);
        $this->assertEquals($mappings, self::$HELPER->getMappings($alias));
    }

    public function testGetListAliasEmpty()
    {
        $this->assertEquals([], self::$HELPER->getListAlias());
    }

    public function testGetListAlias()
    {
        $alias1 = 'financial';
        $alias2 = 'football';
        self::$HELPER->createIndex($alias1);
        self::$HELPER->createIndex($alias2);

        $aliases = self::$HELPER->getListAlias();

        $this->assertTrue(in_array($alias1, $aliases));
        $this->assertTrue(in_array($alias2, $aliases));
        $this->assertTrue(count($aliases) === 2);
    }

    /**
     * @expectedException  \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function addDocumentIndexNotFound()
    {
        $alias = 'myindex';
        self::$HELPER->addDocument($alias, 'test', []);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function addDocument($alias)
    {
        $type = 'test';
        $body = [
            'test' => 'Palatii dicto sciens venit contumaciter'
        ];
        $this->assertTrue(self::$HELPER->addDocument($alias, $type, $body));
    }

    /**
     * @expectedException  \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function updateDocumentIndexNotFound()
    {
        $alias = 'myindex';
        self::$HELPER->updateDocument($alias, 0, 'test', []);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function updateDocument($alias)
    {
        $type = 'test';
        $body = [
            'test' => 'Palatii dicto sciens venit contumaciter'
        ];

        $this->assertTrue(self::$HELPER->addDocument($alias, $type, $body, 1));

        $body['test2'] = 'Tandem culpa in coalitos innocentium saltem malivolus pro parceretur ut';

        $this->assertTrue(self::$HELPER->updateDocument($alias, 1, $type, $body));
    }

}