<?php

namespace Nexucis\Tests\Elasticsearch\Helper\Nodowntime;

class MappingsActionTest extends AbstractIndexHelperTest
{

    /**
     * @dataProvider aliasDataProvider
     */
    public function testUpdateMappingsEmpty($alias)
    {
        self::$HELPER->createIndex($alias);

        self::$HELPER->updateMappings($alias, array());

        $this->assertTrue(self::$HELPER->existsIndex($alias));
        $this->assertTrue(self::$HELPER->existsIndex($alias . self::$HELPER::INDEX_NAME_CONVENTION_2));
        $this->assertEquals(array(), self::$HELPER->getMappings($alias));
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testUpdateMappingsNull($alias)
    {
        self::$HELPER->createIndex($alias);

        self::$HELPER->updateMappings($alias, null);

        $this->assertTrue(self::$HELPER->existsIndex($alias));
        $this->assertTrue(self::$HELPER->existsIndex($alias . self::$HELPER::INDEX_NAME_CONVENTION_2));
        $this->assertEquals(array(), self::$HELPER->getMappings($alias));
    }

    /**
     * @expectedException \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function testUpdateMappingsIndexNotFoundException()
    {
        $aliasSrc = 'myindextest';

        self::$HELPER->updateMappings($aliasSrc, array());
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testUpdateMappingsBasicData($alias)
    {
        $mapping = [
            'my_type' => [
                'properties' => [
                    'first_name' => [
                        'type' => 'string',
                        'analyzer' => 'standard'
                    ],
                    'age' => [
                        'type' => 'integer'
                    ]
                ]
            ]
        ];

        self::$HELPER->createIndex($alias);

        self::$HELPER->updateMappings($alias, $mapping);
        $this->assertTrue(self::$HELPER->existsIndex($alias));
        $this->assertTrue(self::$HELPER->existsIndex($alias . self::$HELPER::INDEX_NAME_CONVENTION_2));
        $this->assertEquals($mapping, self::$HELPER->getMappings($alias));
    }

    public function testUpdateMappingsWithIndexNotEmpty()
    {
        $type = 'complains';
        $alias = 'financial';
        // create index with some contents
        $this->loadFinancialIndex($alias, $type);

        $mapping = [
            $type => [
                'properties' => [
                    'viewType' => [
                        'type' => 'string',
                        'index' => 'no'
                    ]
                ]
            ]
        ];

        self::$HELPER->updateMappings($alias, $mapping, true);
        $this->assertTrue(self::$HELPER->existsIndex($alias));
        $this->assertTrue(self::$HELPER->existsIndex($alias . self::$HELPER::INDEX_NAME_CONVENTION_2));
        $this->assertEquals($mapping[$type]['properties']['viewType']['index'], self::$HELPER->getMappings($alias)[$type]['properties']['viewType']['index']);

        $this->assertTrue($this->countDocuments($alias) > 0);
    }

}
