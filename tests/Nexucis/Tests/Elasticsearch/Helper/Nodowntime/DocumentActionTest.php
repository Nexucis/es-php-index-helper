<?php

namespace Nexucis\Tests\Elasticsearch\Helper\Nodowntime;

class DocumentActionTest extends AbstractIndexHelperTest
{
    /**
     * @expectedException  \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function testDeleteAllDocumentsIndexNotFound()
    {
        $alias = 'myindex';
        $this->helper->deleteAllDocuments($alias);
    }

    public function testDeleteAllDocuments()
    {
        $alias = 'financial';
        $this->loadFinancialIndex($alias);

        $mappings = $this->helper->getMappings($alias);

        $this->assertTrue($this->countDocuments($alias) > 0);

        $this->helper->deleteAllDocuments($alias);

        $this->assertTrue($this->countDocuments($alias) == 0);
        $this->assertEquals($mappings, $this->helper->getMappings($alias));
    }

    public function testGetListAliasEmpty()
    {
        $this->assertEquals([], $this->helper->getListAlias());
    }

    public function testGetListAlias()
    {
        $alias1 = 'financial';
        $alias2 = 'football';
        $this->helper->createIndex($alias1);
        $this->helper->createIndex($alias2);

        $aliases = $this->helper->getListAlias();

        $this->assertTrue(in_array($alias1, $aliases));
        $this->assertTrue(in_array($alias2, $aliases));
        $this->assertTrue(count($aliases) === 2);
    }

    /**
     * @expectedException  \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function testAddDocumentIndexNotFound()
    {
        $alias = 'myindex';
        $this->helper->addDocument($alias, 'test', []);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testAddDocument($alias)
    {
        $type = 'test';
        $body = [
            'test' => 'Palatii dicto sciens venit contumaciter'
        ];

        $this->helper->createIndex($alias);

        $this->assertTrue($this->helper->addDocument($alias, $type, $body));
    }

    /**
     * @expectedException  \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function testUpdateDocumentIndexNotFound()
    {
        $alias = 'myindex';
        $this->helper->updateDocument($alias, 0, 'test', []);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testUpdateDocument($alias)
    {
        $type = 'test';
        $id = 1;
        $body = [
            'test' => 'Palatii dicto sciens venit contumaciter'
        ];

        $this->helper->createIndex($alias);

        $this->assertTrue($this->helper->addDocument($alias, $type, $body, $id));

        $body['test2'] = 'Tandem culpa in coalitos innocentium saltem malivolus pro parceretur ut';

        $this->assertTrue($this->helper->updateDocument($alias, $id, $type, $body));
    }

    /**
     * @expectedException  \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function testDeleteDocumentIndexNotFound()
    {
        $alias = 'myindex';
        $this->helper->deleteDocument($alias, 0, 'test');
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testDeleteDocument($alias)
    {
        $type = 'test';
        $id = 0;
        $body = [
            'test' => 'Palatii dicto sciens venit contumaciter'
        ];

        $this->helper->createIndex($alias);

        $this->assertTrue($this->helper->addDocument($alias, $type, $body, $id));

        $this->helper->deleteDocument($alias, $id, $type);
        $param = array(
            'id' => $id,
            'type' => $type,
            'index' => $alias
        );

        $this->expectException(\Elasticsearch\Common\Exceptions\Missing404Exception::class);
        $this->client->get($param);
    }

    /**
     * @dataProvider aliasDataProvider
     * @expectedException \Elasticsearch\Common\Exceptions\Missing404Exception
     */
    public function testDocumentNotExist($alias)
    {
        $type = 'test';
        $id = 0;
        $this->helper->createIndex($alias);
        $this->helper->deleteDocument($alias, $id, $type);
    }

    /**
     * @expectedException  \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function testGetAllDocumentIndexNotFound()
    {
        $alias = 'myindex';
        $this->helper->getAllDocuments($alias);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testGetAllDocumentIndexEmpty($alias)
    {
        $this->helper->createIndex($alias);
        $result = $this->helper->getAllDocuments($alias);

        $this->assertTrue($result['hits']['total'] === 0);
        $this->assertTrue(count($result['hits']['hits']) === 0);
    }

    public function testGetAllDocument()
    {
        $alias = 'financial';
        // create index with some contents
        $this->loadFinancialIndex($alias);

        $result = $this->helper->getAllDocuments($alias);

        $this->assertTrue($result['hits']['total'] > 10);
        $this->assertTrue(count($result['hits']['hits']) === 10);
    }
}
