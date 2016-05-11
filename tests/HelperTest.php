<?php
namespace Salesking\Tests\PHPSDK;

use Salesking\PHPSDK\Exception;
use Salesking\PHPSDK\Helper;

/**
 * @version     1.0.0
 * @package     SalesKing PHP SDK Tests
 * @license     MIT License; see LICENSE
 * @copyright   Copyright (C) 2012 David Jardin
 * @link        http://www.salesking.eu
 */
class HelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Helper::pluralize
     */
    public function testPluralizePluralizesObjectNames()
    {
        $this->assertEquals("contacts", Helper::pluralize("contact"));
        $this->assertEquals("companies", Helper::pluralize("company"));

    }

    /**
     * @covers Helper::loadSchema
     */
    public function testLoadsSchemaAndReturnsObject()
    {
        $this->assertInstanceOf("\stdClass", Helper::loadSchema("contact"));
    }

    /**
     * @covers Helper::loadSchema
     *
     * @expectedException \Salesking\PHPSDK\Exception
     * @expectedExceptionCode SCHEMA_NOTFOUND
     * @expectedExceptionMessage Could not find schema file.
     */
    public function testLoadSchemaThrowsExceptionOnInvalidSchemaName()
    {
        Helper::loadSchema("notexisting");
    }

    /**
     * @covers Helper::loadSchema
     *
     * @expectedException \Salesking\PHPSDK\Exception
     * @expectedExceptionCode INVALID_SCHEMA
     * @expectedExceptionMessage Schema is invalid JSON
     */
    public function testLoadSchemaThrowsExceptionOnInvalidSchemaSyntax()
    {
        copy(dirname(__FILE__) . '/data/invalidjson.json', dirname(__FILE__) . '/../src/schemes/invalidjson.json');

        try {
            Helper::loadSchema("invalidjson");
        } catch (Exception $e) {
            unlink(dirname(__FILE__) . '/../src/schemes/invalidjson.json');

            throw $e;
        }

        unlink(dirname(__FILE__) . '/../src/schemes/invalidjson.json');
    }
}
