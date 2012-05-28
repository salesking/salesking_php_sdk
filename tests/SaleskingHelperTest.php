<?php
/**
 * @version     1.0.0
 * @package     SalesKing PHP SDK Tests
 * @license     MIT License; see LICENSE
 * @copyright   Copyright (C) 2012 David Jardin
 * @link        http://www.salesking.eu
 */
require_once (dirname(__FILE__).'/../src/salesking.php');
class SaleskingHelperTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SaleskingHelper
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers SaleskingHelper::pluralize
     */
    public function testPluralize()
    {
        $this->assertEquals("clients",SaleskingHelper::pluralize("client"));
        $this->assertEquals("companies",SaleskingHelper::pluralize("company"));

    }

    /**
     * @covers SaleskingHelper::loadSchema
     */
    public function testLoadSchema()
    {
        // not existing schema cant be called
        $thrown = false;
        try {
            SaleskingHelper::loadSchema("notexisting");
        }
        catch (SaleskingException $e)
        {
            if($e->getCode() == "SCHEMA_NOTFOUND" AND $e->getMessage() == "Could not find schema file."){
                $thrown = true;
            }
        }
        $this->assertTrue($thrown);

        $this->assertInstanceOf("stdClass",SaleskingHelper::loadSchema("client"));
    }
}
