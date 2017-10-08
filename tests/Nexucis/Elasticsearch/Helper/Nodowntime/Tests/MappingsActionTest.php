<?php

namespace Nexucis\Elasticsearch\Helper\Nodowntime\Tests;

class MappingsActionTest extends AbstractIndexHelperTest
{

    public function testUpdateMappingsEmpty()
    {
        $aliasSrc = 'myindextest';
        self::$HELPER->createIndex($aliasSrc);

        self::$HELPER->updateMappings($aliasSrc, array());

        $this->assertTrue(self::$HELPER->existsIndex($aliasSrc));
        $this->assertTrue(self::$HELPER->existsIndex($aliasSrc . self::$HELPER::INDEX_NAME_CONVENTION_2));
        $this->assertEquals(array(), self::$HELPER->getMappings($aliasSrc));
    }

    public function testUpdateMappingsNull()
    {
        $aliasSrc = 'myindextest';
        self::$HELPER->createIndex($aliasSrc);

        self::$HELPER->updateMappings($aliasSrc, null);

        $this->assertTrue(self::$HELPER->existsIndex($aliasSrc));
        $this->assertTrue(self::$HELPER->existsIndex($aliasSrc . self::$HELPER::INDEX_NAME_CONVENTION_2));
        $this->assertEquals(array(), self::$HELPER->getMappings($aliasSrc));
    }

    /**
     * @expectedException \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function testUpdateMappingsIndexNotFoundException()
    {
        $aliasSrc = 'myindextest';

        self::$HELPER->updateMappings($aliasSrc, array());
    }

}
