<?php

namespace Nexucis\Elasticsearch\Helper\Nodowntime\Tests;


class SettingsActionTest extends AbstractIndexHelperTest
{


    /**
     * @expectedException \Elasticsearch\Common\Exceptions\InvalidArgumentException
     */
    public function testAddSettingsEmpty()
    {
        $aliasSrc = 'myindextest';
        self::$HELPER->createIndex($aliasSrc);

        self::$HELPER->addSettings($aliasSrc, array());
    }

    /**
     * @expectedException \Elasticsearch\Common\Exceptions\InvalidArgumentException
     */
    public function testAddSettingsNull()
    {
        $aliasSrc = 'myindextest';
        self::$HELPER->createIndex($aliasSrc);

        self::$HELPER->addSettings($aliasSrc, null);
    }

    /**
     * @expectedException \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function testAddSettingsIndexNotFoundException()
    {
        $aliasSrc = 'myindextest';
        self::$HELPER->addSettings($aliasSrc, null);
    }

    public function testUpdateSettingsEmpty()
    {
        $aliasSrc = 'myindextest';
        self::$HELPER->createIndex($aliasSrc);

        self::$HELPER->updateSettings($aliasSrc, array());

        $this->assertTrue(self::$HELPER->existsIndex($aliasSrc));
        $this->assertTrue(self::$HELPER->existsIndex($aliasSrc . self::$HELPER::INDEX_NAME_CONVENTION_2));
        $this->assertFalse(array_key_exists('analysis', self::$HELPER->getSettings($aliasSrc)));
    }

    public function testUpdateSettingsNull()
    {
        $aliasSrc = 'myindextest';
        self::$HELPER->createIndex($aliasSrc);

        self::$HELPER->updateSettings($aliasSrc, null);

        $this->assertTrue(self::$HELPER->existsIndex($aliasSrc));
        $this->assertTrue(self::$HELPER->existsIndex($aliasSrc . self::$HELPER::INDEX_NAME_CONVENTION_2));
        $this->assertFalse(array_key_exists('analysis', self::$HELPER->getSettings($aliasSrc)));
    }

    /**
     * @expectedException \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function testUpdateSettingsIndexNotFound()
    {
        $aliasSrc = 'myindextest';
        self::$HELPER->updateSettings($aliasSrc, array());
    }

}