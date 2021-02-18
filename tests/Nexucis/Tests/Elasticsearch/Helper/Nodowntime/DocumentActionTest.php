<?php

namespace Nexucis\Tests\Elasticsearch\Helper\Nodowntime;

use Elasticsearch\Common\Exceptions\Missing404Exception;
use Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException;
use Nexucis\Elasticsearch\Helper\Nodowntime\Parameter\SearchParameter;
use stdClass;

class DocumentActionTest extends AbstractIndexHelperTest
{
    public function testDeleteAllDocumentsIndexNotFound()
    {
        $alias = 'myindex';

        $this->expectException(IndexNotFoundException::class);

        $this->helper->deleteAllDocuments($alias);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testDeleteAllDocuments(string $alias)
    {
        $this->loadFinancialIndex($alias);

        $mappings = $this->helper->getMappings($alias);

        $this->assertTrue($this->countDocuments($alias) > 0);

        $this->helper->deleteAllDocuments($alias);

        $this->assertTrue($this->countDocuments($alias) == 0);
        $this->assertEquals($mappings, $this->helper->getMappings($alias));
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testDeleteAllDocumentsIndexAlreadyExists(string $alias)
    {
        $this->loadFinancialIndex($alias);

        $mappings = $this->helper->getMappings($alias);

        $this->assertTrue($this->countDocuments($alias) > 0);

        // create the target index in order to check if the method will delete it
        $this->createIndex2($alias);

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
        $this->helper->createIndexByAlias($alias1);
        $this->helper->createIndexByAlias($alias2);

        $aliases = $this->helper->getListAlias();

        $this->assertTrue(in_array($alias1, $aliases));
        $this->assertTrue(in_array($alias2, $aliases));
        $this->assertTrue(count($aliases) === 2);
    }

    public function testGetDocumentIndexNotFound()
    {
        $alias = 'myindex';

        $this->expectException(IndexNotFoundException::class);

        $this->helper->getDocument($alias, 'test', 'id');
    }

    public function testAddDocumentIndexNotFound()
    {
        $alias = 'myindex';

        $this->expectException(IndexNotFoundException::class);

        $this->helper->addDocument($alias, 'test', []);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testAddAndGetDocument(string $alias)
    {
        $type = 'test';
        $body = [
            'test' => 'Palatii dicto sciens venit contumaciter'
        ];
        $id = 'randomId';

        $this->helper->createIndexByAlias($alias);

        $this->assertTrue($this->helper->addDocument($alias, $type, $body, $id, true));

        $document = $this->helper->getDocument($alias, $type, $id);
        $this->assertSame($body['test'], $document['_source']['test']);
    }

    public function testUpdateDocumentIndexNotFound()
    {
        $alias = 'myindex';

        $this->expectException(IndexNotFoundException::class);

        $this->helper->updateDocument($alias, 0, 'test', []);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testUpdateDocument(string $alias)
    {
        $type = 'test';
        $id = 1;
        $body = [
            'test' => 'Palatii dicto sciens venit contumaciter'
        ];

        $this->helper->createIndexByAlias($alias);

        $this->assertTrue($this->helper->addDocument($alias, $type, $body, $id));

        $body['test2'] = 'Tandem culpa in coalitos innocentium saltem malivolus pro parceretur ut';

        $this->assertTrue($this->helper->updateDocument($alias, $id, $type, $body));
    }

    public function testDeleteDocumentIndexNotFound()
    {
        $alias = 'myindex';

        $this->expectException(IndexNotFoundException::class);

        $this->helper->deleteDocument($alias, 0, 'test');
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testDeleteDocument(string $alias)
    {
        $type = 'test';
        $id = 0;
        $body = [
            'test' => 'Palatii dicto sciens venit contumaciter'
        ];

        $this->helper->createIndexByAlias($alias);

        $this->assertTrue($this->helper->addDocument($alias, $type, $body, $id, true));

        $this->helper->deleteDocument($alias, $id, $type, true);
        $param = array(
            'id' => $id,
            'type' => $type,
            'index' => $alias
        );

        $this->expectException(Missing404Exception::class);
        $this->client->get($param);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testDocumentNotExist(string $alias)
    {
        $type = 'test';
        $id = 0;
        $this->helper->createIndexByAlias($alias);

        $this->expectException(Missing404Exception::class);

        $this->helper->deleteDocument($alias, $id, $type);
    }

    public function testGetAllDocumentIndexNotFound()
    {
        $alias = 'myindex';

        $this->expectException(IndexNotFoundException::class);

        $this->helper->getAllDocuments($alias);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testGetAllDocumentIndexEmpty(string $alias)
    {
        $this->helper->createIndexByAlias($alias);
        $result = $this->helper->getAllDocuments($alias);

        $this->assertTrue($result['hits']['total']['value'] === 0);
        $this->assertTrue(count($result['hits']['hits']) === 0);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testGetAllDocument(string $alias)
    {
        // create index with some contents
        $this->loadFinancialIndex($alias);

        $result = $this->helper->getAllDocuments($alias);

        $this->assertTrue($result['hits']['total']['value'] > 10);
        $this->assertTrue(count($result['hits']['hits']) === 10);
    }

    public function testSearchDocumentsIndexNotFound()
    {
        $alias = 'myindex';

        $this->expectException(IndexNotFoundException::class);

        $this->helper->searchDocuments($alias);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testSearchDocumentsIndexEmpty(string $alias)
    {
        $this->helper->createIndexByAlias($alias);
        $result = $this->helper->searchDocuments($alias);

        $this->assertTrue($result['hits']['total']['value'] === 0);
        $this->assertTrue(count($result['hits']['hits']) === 0);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testSearchDocuments(string $alias)
    {
        // create index with some contents
        $this->loadFinancialIndex($alias);

        $query = array(
            'match_all' => new stdClass()
        );

        $result = $this->helper->searchDocuments($alias, $query);

        $this->assertTrue($result['hits']['total']['value'] > 10);
        $this->assertTrue(count($result['hits']['hits']) === 10);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testSearchDocumentsWithSource(string $alias)
    {
        $expectedFields = array(
            'name',
            'id'
        );
        // create index with some contents
        $this->loadFinancialIndex($alias);

        $body = array(
            'query' => array(
                'match_all' => new stdClass()
            )
        );

        $result = $this->helper->advancedSearchDocument(
            $alias,
            null,
            $body,
            (new SearchParameter())
                ->from(0)
                ->size(10)
                ->includeSource($expectedFields)
        );

        $this->assertTrue($result['hits']['total']['value'] > 10);
        $this->assertTrue(count($result['hits']['hits']) === 10);

        foreach ($result['hits']['hits'] as $item) {
            $actualFields = array_keys($item['_source']);
            $this->assertEquals($expectedFields, $actualFields);
        }
    }
}
