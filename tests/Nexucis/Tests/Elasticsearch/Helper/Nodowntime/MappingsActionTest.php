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

    /**
     * @dataProvider aliasDataProvider
     */
    public function testUpdateMappingWithSettingsNotEmpty($alias)
    {
        $settings = [
            'number_of_shards' => 1,
            'number_of_replicas' => 0,
            'analysis' => [
                'filter' => [
                    'shingle' => [
                        'type' => 'shingle'
                    ]
                ],
                'char_filter' => [
                    'pre_negs' => [
                        'type' => 'pattern_replace',
                        'pattern' => '(\\w+)\\s+((?i:never|no|nothing|nowhere|noone|none|not|havent|hasnt|hadnt|cant|couldnt|shouldnt|wont|wouldnt|dont|doesnt|didnt|isnt|arent|aint))\\b',
                        'replacement' => '~$1 $2'
                    ],
                    'post_negs' => [
                        'type' => 'pattern_replace',
                        'pattern' => '\\b((?i:never|no|nothing|nowhere|noone|none|not|havent|hasnt|hadnt|cant|couldnt|shouldnt|wont|wouldnt|dont|doesnt|didnt|isnt|arent|aint))\\s+(\\w+)',
                        'replacement' => '$1 ~$2'
                    ]
                ],
                'analyzer' => [
                    'reuters' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => ['lowercase', 'stop', 'kstem']
                    ]
                ]
            ]
        ];

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
        self::$HELPER->updateSettings($alias, $settings);

        self::$HELPER->updateMappings($alias, $mapping);
        $this->assertTrue(self::$HELPER->existsIndex($alias));
        $this->assertTrue(self::$HELPER->existsIndex($alias . self::$HELPER::INDEX_NAME_CONVENTION_1));
        $this->assertEquals($mapping, self::$HELPER->getMappings($alias));

        $resultSettings = self::$HELPER->getSettings($alias);
        $this->assertTrue(array_key_exists('analysis', $resultSettings));
        $this->assertEquals($settings['analysis'], $resultSettings['analysis']);
        $this->assertEquals($settings['number_of_shards'], $resultSettings['number_of_shards']);
        $this->assertEquals($settings['number_of_replicas'], $resultSettings['number_of_replicas']);
    }

    /**
     * @expectedException \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function testGetMappingsIndexNotFound()
    {
        $alias = 'myindex';
        self::$HELPER->getMappings($alias);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testGetMappingsEmptyIndex($alias)
    {
        self::$HELPER->createIndex($alias);

        $this->assertEquals([], self::$HELPER->getMappings($alias));
    }
}
