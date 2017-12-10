<?php

namespace Nexucis\Tests\Elasticsearch\Helper\Nodowntime;

class MappingsActionTest extends AbstractIndexHelperTest
{

    /**
     * @dataProvider aliasDataProvider
     */
    public function testUpdateMappingsEmpty($alias)
    {
        $this->helper->createIndexByAlias($alias);

        $this->helper->updateMappings($alias, array());

        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_2));
        $this->assertEquals(array(), $this->helper->getMappings($alias));
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testUpdateMappingsNull($alias)
    {
        $this->helper->createIndexByAlias($alias);

        $this->helper->updateMappings($alias, null);

        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_2));
        $this->assertEquals(array(), $this->helper->getMappings($alias));
    }

    /**
     * @expectedException \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function testUpdateMappingsIndexNotFoundException()
    {
        $aliasSrc = 'myindextest';

        $this->helper->updateMappings($aliasSrc, array());
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
                        'type' => 'text',
                        'analyzer' => 'standard'
                    ],
                    'age' => [
                        'type' => 'integer'
                    ]
                ]
            ]
        ];

        $this->helper->createIndexByAlias($alias);

        $this->helper->updateMappings($alias, $mapping);
        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_2));
        $this->assertEquals($mapping, $this->helper->getMappings($alias));
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
                        'type' => 'text',
                        'index' => false
                    ]
                ]
            ]
        ];

        $this->helper->updateMappings($alias, $mapping, true);
        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_2));
        $this->assertEquals($mapping[$type]['properties']['viewType']['index'], $this->helper->getMappings($alias)[$type]['properties']['viewType']['index']);

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
                        'type' => 'text',
                        'analyzer' => 'standard'
                    ],
                    'age' => [
                        'type' => 'integer'
                    ]
                ]
            ]
        ];

        $this->helper->createIndexByAlias($alias);
        $this->helper->updateSettings($alias, $settings);

        $this->helper->updateMappings($alias, $mapping);
        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_1));
        $this->assertEquals($mapping, $this->helper->getMappings($alias));

        $resultSettings = $this->helper->getSettings($alias);
        $this->assertTrue(array_key_exists('analysis', $resultSettings));
        $this->assertEquals($settings['analysis'], $resultSettings['analysis']);
        $this->assertEquals($settings['number_of_shards'], $resultSettings['number_of_shards']);
        $this->assertEquals($settings['number_of_replicas'], $resultSettings['number_of_replicas']);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testUpdateMappingIndexAlreadyExists(string $alias)
    {
        $this->helper->createIndexByAlias($alias);
        $this->createIndex2($alias);

        $this->helper->updateMappings($alias, null);

        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_2));
        $this->assertEquals(array(), $this->helper->getMappings($alias));
    }

    /**
     * @expectedException \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function testGetMappingsIndexNotFound()
    {
        $alias = 'myindex';
        $this->helper->getMappings($alias);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testGetMappingsEmptyIndex($alias)
    {
        $this->helper->createIndexByAlias($alias);

        $this->assertEquals([], $this->helper->getMappings($alias));
    }
}
