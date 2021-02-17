<?php

namespace Nexucis\Tests\Elasticsearch\Helper\Nodowntime;

use Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException;

class MappingsActionTest extends AbstractIndexHelperTest
{

    /**
     * @dataProvider aliasDataProviderWithTypeName
     */
    public function testUpdateMappingsEmpty(string $alias, bool $includeTypeName)
    {
        $this->helper->createIndexByAlias($alias);

        $this->helper->updateMappings($alias, array(), false, true, true, $includeTypeName);

        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_2));
        $this->assertEquals(array(), $this->helper->getMappings($alias));
    }

    /**
     * @dataProvider aliasDataProviderWithTypeName
     */
    public function testUpdateMappingsNull(string $alias, bool $includeTypeName)
    {
        $this->helper->createIndexByAlias($alias);

        $this->helper->updateMappings($alias, null, false, true, true, $includeTypeName);

        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_2));
        $this->assertEquals(array(), $this->helper->getMappings($alias));
    }

    public function testUpdateMappingsIndexNotFoundException()
    {
        $aliasSrc = 'myindextest';

        $this->expectException(IndexNotFoundException::class);

        $this->helper->updateMappings($aliasSrc, array());
    }

    /**
     * @dataProvider aliasDataProviderWithTypeName
     */
    public function testUpdateMappingsBasicData(string $alias, bool $includeTypeName)
    {
        if ($includeTypeName) {
            $mapping = [
                'my_type' => [
                    'properties' => [
                        'first_name' => [
                            'type' => 'text',
                            'analyzer' => 'standard'
                        ],
                        'age' => [
                            'type' => 'integer'
                        ]
                    ]
                ]
            ];
        } else {
            $mapping = [
                'properties' => [
                    'first_name' => [
                        'type' => 'text',
                        'analyzer' => 'standard'
                    ],
                    'age' => [
                        'type' => 'integer'
                    ]
                ]
            ];
        }

        $mappingExpected = [
            'properties' => [
                'first_name' => [
                    'type' => 'text',
                    'analyzer' => 'standard'
                ],
                'age' => [
                    'type' => 'integer'
                ]
            ]
        ];

        $this->helper->createIndexByAlias($alias);

        $this->helper->updateMappings($alias, $mapping, true, true, true, $includeTypeName);
        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_2));
        $this->assertEquals($mappingExpected, $this->helper->getMappings($alias));
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testUpdateMappingsWithIndexNotEmpty(string $alias)
    {
        // create index with some contents
        $this->loadFinancialIndex($alias);

        $mapping = [
            'properties' => [
                'viewType' => [
                    'type' => 'text',
                    'index' => false
                ]
            ]
        ];

        $this->helper->updateMappings($alias, $mapping, true, true, true, false);
        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_2));
        $this->assertEquals($mapping['properties']['viewType']['index'], $this->helper->getMappings($alias)['properties']['viewType']['index']);

        $this->assertTrue($this->countDocuments($alias) > 0);
    }

    /**
     * @dataProvider aliasDataProviderWithTypeName
     */
    public function testUpdateMappingWithSettingsNotEmpty(string $alias, bool $includeTypeName)
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

        if ($includeTypeName) {
            $mapping = [
                'my_type' => [
                    'properties' => [
                        'first_name' => [
                            'type' => 'text',
                            'analyzer' => 'standard'
                        ],
                        'age' => [
                            'type' => 'integer'
                        ]
                    ]
                ]
            ];
        } else {
            $mapping = [
                'properties' => [
                    'first_name' => [
                        'type' => 'text',
                        'analyzer' => 'standard'
                    ],
                    'age' => [
                        'type' => 'integer'
                    ]
                ]
            ];
        }

        $mappingExpected = [
            'properties' => [
                'first_name' => [
                    'type' => 'text',
                    'analyzer' => 'standard'
                ],
                'age' => [
                    'type' => 'integer'
                ]
            ]
        ];


        $this->helper->createIndexByAlias($alias);
        $this->helper->updateSettings($alias, $settings, $includeTypeName);

        $this->helper->updateMappings($alias, $mapping, true, true, true, $includeTypeName);
        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_1));
        $this->assertEquals($mappingExpected, $this->helper->getMappings($alias));

        $resultSettings = $this->helper->getSettings($alias);
        $this->assertTrue(array_key_exists('analysis', $resultSettings));
        $this->assertEquals($settings['analysis'], $resultSettings['analysis']);
        $this->assertEquals($settings['number_of_shards'], $resultSettings['number_of_shards']);
        $this->assertEquals($settings['number_of_replicas'], $resultSettings['number_of_replicas']);
    }

    /**
     * @dataProvider aliasDataProviderWithTypeName
     */
    public function testUpdateMappingsIndexAlreadyExists(string $alias, bool $includeTypeName)
    {
        $this->helper->createIndexByAlias($alias);
        $this->createIndex2($alias);

        $this->helper->updateMappings($alias, null, true, true, true, $includeTypeName);

        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_2));
        $this->assertEquals(array(), $this->helper->getMappings($alias));
    }

    /**
     * @dataProvider aliasDataProviderWithTypeName
     */
    public function testUpdateMappingsWithoutReindexation(string $alias, bool $includeTypeName)
    {
        $this->helper->createIndexByAlias($alias);

        $this->assertEquals($this->helper::RETURN_ACKNOWLEDGE, $this->helper->updateMappings($alias, null, false, false, true, $includeTypeName));

        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_1));
    }

    public function testGetMappingsIndexNotFound()
    {
        $alias = 'myindex';

        $this->expectException(IndexNotFoundException::class);

        $this->helper->getMappings($alias);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testGetMappingsEmptyIndex(string $alias)
    {
        $this->helper->createIndexByAlias($alias);

        $this->assertEquals([], $this->helper->getMappings($alias));
    }
}
