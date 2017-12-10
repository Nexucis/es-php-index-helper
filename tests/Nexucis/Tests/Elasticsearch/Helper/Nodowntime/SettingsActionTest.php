<?php

namespace Nexucis\Tests\Elasticsearch\Helper\Nodowntime;

class SettingsActionTest extends AbstractIndexHelperTest
{

    /**
     * @expectedException \Elasticsearch\Common\Exceptions\InvalidArgumentException
     */
    public function testAddSettingsEmpty()
    {
        $aliasSrc = 'myindextest';
        $this->helper->createIndexByAlias($aliasSrc);

        $this->helper->addSettings($aliasSrc, array());
    }

    /**
     * @expectedException \Elasticsearch\Common\Exceptions\InvalidArgumentException
     */
    public function testAddSettingsNull()
    {
        $aliasSrc = 'myindextest';
        $this->helper->createIndexByAlias($aliasSrc);

        $this->helper->addSettings($aliasSrc, null);
    }

    /**
     * @expectedException \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function testAddSettingsIndexNotFoundException()
    {
        $aliasSrc = 'myindextest';
        $this->helper->addSettings($aliasSrc, null);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testAddSettingsBasicData(string $alias)
    {
        $this->helper->createIndexByAlias($alias);
        $settings = [
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

        // we need to wait a moment because ElasticSearch need to synchronize something before we can close a creating index.
        // ElasticSearch Issue  : https://github.com/elastic/elasticsearch/issues/3313
        // Idea to improve this workaround : use _cat/shards endpoint to get the shard status
        sleep(2);
        $this->helper->addSettings($alias, $settings);

        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_1));

        $resultSettings = $this->helper->getSettings($alias);
        $this->assertTrue(array_key_exists('analysis', $resultSettings));
        $this->assertEquals($settings['analysis'], $resultSettings['analysis']);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testUpdateSettingsEmpty(string $alias)
    {
        $this->helper->createIndexByAlias($alias);

        $this->helper->updateSettings($alias, array());

        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_2));
        $this->assertFalse(array_key_exists('analysis', $this->helper->getSettings($alias)));
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testUpdateSettingsNull(string $alias)
    {
        $this->helper->createIndexByAlias($alias);

        $this->helper->updateSettings($alias, null);

        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_2));
        $this->assertFalse(array_key_exists('analysis', $this->helper->getSettings($alias)));
    }

    /**
     * @expectedException \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function testUpdateSettingsIndexNotFound()
    {
        $aliasSrc = 'myindextest';
        $this->helper->updateSettings($aliasSrc, array());
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testUpdateSettingsBasicData(string $alias)
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
        $this->helper->createIndexByAlias($alias);

        $this->helper->updateSettings($alias, $settings);

        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_2));

        $resultSettings = $this->helper->getSettings($alias);
        $this->assertTrue(array_key_exists('analysis', $resultSettings));
        $this->assertEquals($settings['analysis'], $resultSettings['analysis']);
        $this->assertEquals($settings['number_of_shards'], $resultSettings['number_of_shards']);
        $this->assertEquals($settings['number_of_replicas'], $resultSettings['number_of_replicas']);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testUpdateSettingsWithIndexNotEmpty(string $alias)
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

        // create index with some contents
        $this->loadFinancialIndex($alias);
        $mappings = $this->helper->getMappings($alias);

        $this->helper->updateSettings($alias, $settings, true);
        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_2));

        $resultSettings = $this->helper->getSettings($alias);
        $this->assertTrue(array_key_exists('analysis', $resultSettings));
        $this->assertEquals($settings['analysis'], $resultSettings['analysis']);
        $this->assertEquals($settings['number_of_shards'], $resultSettings['number_of_shards']);
        $this->assertEquals($settings['number_of_replicas'], $resultSettings['number_of_replicas']);

        $this->assertTrue($this->countDocuments($alias) > 0);
        $this->assertEquals($mappings, $this->helper->getMappings($alias));
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testUpdateSettingsWithIndexAlreadyExists(string $alias)
    {
        $this->helper->createIndexByAlias($alias);
        $this->createIndex2($alias);

        $this->helper->updateSettings($alias, null);

        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_2));
        $this->assertFalse(array_key_exists('analysis', $this->helper->getSettings($alias)));
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testUpdateSettingsWithoutReindexation(string $alias)
    {
        $this->helper->createIndexByAlias($alias);

        $this->assertEquals($this->helper::RETURN_ACKNOWLEDGE, $this->helper->updateSettings($alias, null, false, false));

        $this->assertTrue($this->helper->existsIndex($alias));
        $this->assertTrue($this->helper->existsIndex($alias . $this->helper::INDEX_NAME_CONVENTION_1));
    }

    /**
     * @expectedException \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function testGetSettingsIndexNotFoundException()
    {
        $aliasSrc = 'myindextest';
        $this->helper->getSettings($aliasSrc);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testGetSettingsEmptyIndex(string $alias)
    {
        $this->helper->createIndexByAlias($alias);

        $this->assertFalse(array_key_exists('analysis', $this->helper->getSettings($alias)));
    }
}
