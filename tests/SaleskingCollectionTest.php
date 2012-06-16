<?php
/**
 * @version     1.0.0
 * @package     SalesKing PHP SDK Tests
 * @license     MIT License; see LICENSE
 * @copyright   Copyright (C) 2012 David Jardin
 * @link        http://www.salesking.eu
 */
require_once (dirname(__FILE__).'/../src/salesking.php');

class SaleskingCollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SaleskingCollection
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $api = $this->getMock("Salesking",array(),array(),'',false);

        $api->expects($this->any())
            ->method("request")
            ->will(
            $this->returnCallback(array($this,'getMockRequest'))
        );

        $api->expects($this->any())
            ->method("getObject")
            ->will(
            $this->returnCallback(array($this,'getMockGetObject'))
        );

        $this->object = new SaleskingCollection($api,array("type"=>"client"));
    }

    public function getMockRequest($url,$method="GET",$data=null)
    {
        if($url == "/api/clients?sort=ASC&per_page=100" AND $method == "GET"){
            $response["code"] = "200";
            $body = new stdClass();
            $body->clients = array();

            $collection = new stdClass();
            $collection->current_page = 1;
            $collection->total_pages = 4;
            $collection->per_page = 100;
            $collection->total_entries = 5;

            $body->collection = $collection;

            $client = new stdClass();
            $client->client = new StdClass();
            $client->client->number = "K-01012-800";
            $client->client->organisation = "salesking";
            $body->clients[] = $client;

            $client = new stdClass();
            $client->client = new StdClass();
            $client->client->number = "K-01012-900";
            $client->client->organisation = "examplecompany";
            $body->clients[] = $client;

            $response["body"] = $body;
        }

        return $response;
    }

    public function getMockGetObject($type)
    {
        $config = array(
            "type" => $type
        );

        $api = $this->getMock("Salesking",array(),array(),'',false);

        return new SaleskingObject($api,$config);
    }


    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers SaleskingCollection::__construct
     */
    public function test__construct()
    {
        $thrown = false;
        try
        {
            new SaleskingCollection();
        }
        catch (PHPUnit_Framework_Error $e)
        {
            $thrown = true;
        }
        $this->assertTrue($thrown);

    }

    /**
     * @covers SaleskingCollection::load
     */
    public function testLoad()
    {
        $this->assertInstanceOf("SaleskingCollection",$this->object->load());

        $items = $this->object->getItems();

        $this->assertInstanceOf("SaleskingObject",$items[0]);
    }

    /**
     * @covers SaleskingCollection::getFilters
     */
    public function testGetFilters()
    {
        $this->object->setFilters(array("q" => "salesking","number" => "K-123-0001"));
        $this->assertEquals(array("q" => "salesking","number" => "K-123-0001"),$this->object->getFilters());
    }

    /**
     * @covers SaleskingCollection::setFilters
     */
    public function testSetFilters()
    {
        $this->assertInstanceOf("SaleskingCollection",$this->object->setFilters(array("q" => "salesking","number" => "K-123-0001")));
        $this->assertEquals(array("q" => "salesking","number" => "K-123-0001"),$this->object->getFilters());


        $thrown = false;
        try {
            $this->object->setFilters(array("q" => "salesking","notexisting" => "string"));
        }
        catch (SaleskingException $e)
        {
            if($e->getCode() == "FILTER_NOTEXISTING" AND $e->getMessage() == "Filter does not exist")
            {
                $thrown = true;
            }
        }
        $this->assertTrue($thrown);
    }

    /**
     * @covers SaleskingCollection::addFilter
     */
    public function testAddFilter()
    {
        $this->assertInstanceOf("SaleskingCollection",$this->object->addFilter("q","salesking"));
        $this->assertEquals(array("q" => "salesking"),$this->object->getFilters());

        $thrown = false;
        try {
            $this->object->addFilter("notexisting","string");
        }
        catch (SaleskingException $e)
        {
            if($e->getCode() == "FILTER_NOTEXISTING" AND $e->getMessage() == "Filter does not exist")
            {
                $thrown = true;
            }
        }
        $this->assertTrue($thrown);

        $thrown = false;
        try {
            $this->object->addFilter("birthday_to","string");
        }
        catch (SaleskingException $e)
        {
            if($e->getCode() == "FILTER_INVALID" AND $e->getMessage() == "Invalid filter value")
            {
                $thrown = true;
            }
        }
        $this->assertTrue($thrown);
    }

    /**
     * @covers SaleskingCollection::validateFilter
     */
    public function testValidateFilter()
    {
        // make sure that not existing properties return false
        $this->assertFalse($this->object->validateFilter("notexisting","string"));

        // test property format date
        $this->assertFalse($this->object->validateFilter("birthday_to","1999/01/01"));
        $this->assertFalse($this->object->validateFilter("birthday_to","string"));
        $this->assertFalse($this->object->validateFilter("birthday_to","123"));
        $this->assertFalse($this->object->validateFilter("birthday_to","1999-01-32"));
        $this->assertFalse($this->object->validateFilter("birthday_to","1999-13-01"));
        $this->assertTrue($this->object->validateFilter("birthday_to","1999-01-01"));
    }

    /**
     * @covers SaleskingCollection::getItems
     */
    public function testGetItems()
    {
        $this->object->load();
        $items = $this->object->getItems();
        $this->assertInstanceOf("SaleskingObject",$items[0]);

    }

    /**
     * @covers SaleskingCollection::getType
     */
    public function testGetType()
    {
        $this->object->setType("client");

        $this->assertEquals("client",$this->object->getType());
    }

    /**
     * @covers SaleskingCollection::setType
     */
    public function testSetType()
    {
        $this->object->setType("client");

        $this->assertEquals("client",$this->object->getType());
    }

    /**
     * @covers SaleskingCollection::__call
     */
    public function test__call()
    {
        $this->assertInstanceOf("SaleskingCollection",$this->object->q("salesking"));

        $thrown = false;
        try {
            $this->object->notexisting("string");
        }
        catch (BadMethodCallException $e)
        {
                $thrown = true;
        }
        $this->assertTrue($thrown);

        $thrown = false;
        try {
            $this->object->birthday_to("string");
        }
        catch (SaleskingException $e)
        {
            if($e->getCode() == "FILTER_INVALID" AND $e->getMessage() == "Invalid filter value")
            $thrown = true;
        }
        $this->assertTrue($thrown);
    }

    /**
     * @covers SaleskingCollection::sort
     */
    public function testSort()
    {
        $this->assertInstanceOf("SaleskingCollection",$this->object->sort("ASC"));
        $this->assertInstanceOf("SaleskingCollection",$this->object->sort("DESC"));

        // not existing schema cant be called
        $thrown = false;
        try {
            $this->object->sort("invalid");
        }
        catch (SaleskingException $e)
        {
            if($e->getCode() == "SORT_INVALIDDIRECTION" AND $e->getMessage() == "Invalid sorting direction - please choose either ASC or DESC"){
                $thrown = true;
            }
        }
        $this->assertTrue($thrown);
    }

    /**
     * @covers SaleskingCollection::getSort
     */
    public function testGetSort()
    {
        $this->assertEquals("ASC",$this->object->getSort());

        $this->object->sort("DESC");
        $this->assertEquals("DESC",$this->object->getSort());

    }

    /**
     * @covers SaleskingCollection::sortBy
     */
    public function testSortBy()
    {
        $this->assertInstanceOf("SaleskingCollection",$this->object->sortBy("number"));

        // not existing schema cant be called
        $thrown = false;
        try {
            $this->object->sortBy("invalid");
        }
        catch (SaleskingException $e)
        {
            if($e->getCode() == "SORTBY_INVALIDPROPERTY" AND $e->getMessage() == "Invalid property for sorting"){
                $thrown = true;
            }
        }
        $this->assertTrue($thrown);
    }

    /**
     * @covers SaleskingCollection::getSortBy
     */
    public function testGetSortBy()
    {
        $this->assertEquals("",$this->object->getSortBy());

        $this->object->sortBy("number");
        $this->assertEquals("number",$this->object->getSortBy());
    }

    /**
     * @covers SaleskingCollection::perPage
     */
    public function testPerPage()
    {
        $this->assertInstanceOf("SaleskingCollection",$this->object->perPage(90));

        $thrown = false;
        try {
            $this->object->perPage(101);
        }
        catch (SaleskingException $e)
        {
            if($e->getCode() == "PERPAGE_ONLYINT" AND $e->getMessage() == "Please set an integer <100 for the per-page limit"){
                $thrown = true;
            }
        }
        $this->assertTrue($thrown);

        $thrown = false;
        try {
            $this->object->perPage("string");
        }
        catch (SaleskingException $e)
        {
            if($e->getCode() == "PERPAGE_ONLYINT" AND $e->getMessage() == "Please set an integer <100 for the per-page limit"){
                $thrown = true;
            }
        }
        $this->assertTrue($thrown);
    }

    /**
     * @covers SaleskingCollection::getPerPage
     */
    public function testGetPerPage()
    {
        $this->assertEquals(100,$this->object->getPerPage());

        $this->object->perPage(99);
        $this->assertEquals(99,$this->object->getPerPage());
    }

    /**
     * @covers SaleskingCollection::getTotal
     */
    public function testGetTotal()
    {
        $this->object->load();
        $this->assertEquals(5,$this->object->getTotal());
    }

    /**
     * @covers SaleskingCollection::getTotalPages
     */
    public function testGetTotalPages()
    {
        $this->object->load();
        $this->assertEquals(4,$this->object->getTotalPages());
    }

    /**
     * @covers SaleskingCollection::getCurrentPage
     */
    public function testGetCurrentPage()
    {
        $this->object->load();
        $this->assertEquals(1,$this->object->getCurrentPage());
    }
}
