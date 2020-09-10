<?php
namespace Salesking\Tests\PHPSDK;

/**
 * @version     2.0.0
 * @package     SalesKing PHP SDK Tests
 * @license     MIT License; see LICENSE
 * @copyright   Copyright (C) 2012 David Jardin
 * @link        http://www.salesking.eu
 */
use Salesking\PHPSDK\Exception;

/**
 * Test class for SaleskingException.
 * Generated by PHPUnit on 2012-04-23 at 17:45:46.
 */
class ExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Exception
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Exception("CODE", "MESSAGE", "ERRORS");
    }

    /**
     * @covers Exception::getErrors
     */
    public function testGetErrorsReturnsErrorMessages()
    {
        $this->assertEquals("ERRORS", $this->object->getErrors());
    }

    /**
     * @covers Exception::__construct
     */
    public function testConstructorSetsProperties()
    {
        $exception = new Exception("CODE", "MESSAGE", "ERRORS");

        $this->assertEquals("ERRORS", $exception->getErrors());
        $this->assertEquals("MESSAGE", $exception->getMessage());
        $this->assertEquals("CODE", $exception->getCode());
    }
}
