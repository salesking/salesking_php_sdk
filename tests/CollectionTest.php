<?php
namespace Salesking\Tests\PHPSDK;

use Salesking\PHPSDK\Collection;
use Salesking\PHPSDK\Object;

/**
 * @version     2.0.0
 * @package     SalesKing PHP SDK Tests
 * @license     MIT License; see LICENSE
 * @copyright   Copyright (C) 2012 David Jardin
 * @link        http://www.salesking.eu
 */

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Collection
     */
    protected $object;

    /**
     * Sets up the test object with API mock
     */
    protected function setUp()
    {
        $apiMock = $this->getMock("\\Salesking\\PHPSDK\\API", array(), array(), '', false);

        $apiMock->expects($this->any())
            ->method("request")
            ->will(
                $this->returnCallback(array($this, 'getMockRequest'))
            );

        $apiMock->expects($this->any())
            ->method("getObject")
            ->will(
                $this->returnCallback(array($this, 'getMockGetObject'))
            );

        $this->object = new Collection($apiMock, array("obj_type" => "contact"));
    }

    /**
     * Mock response data for tests
     *
     * @param        $url
     * @param string $method
     * @param null   $data
     *
     * @return array
     */
    public function getMockRequest($url, $method = "GET", $data = null)
    {
        $response = array();

        if ($url == "/api/contacts?sort=ASC&per_page=100" && $method == "GET") {
            $response["code"] = "200";
            $body = new \stdClass();
            $body->contacts = array();

            $collection = new \stdClass();
            $collection->current_page = 1;
            $collection->total_pages = 4;
            $collection->per_page = 100;
            $collection->total_entries = 5;

            $body->collection = $collection;

            $contact = new \stdClass();
            $contact->contact = new \stdClass();
            $contact->contact->number = "K-01012-800";
            $contact->contact->type = "Client";
            $contact->contact->organisation = "salesking";
            $body->contacts[] = $contact;

            $contact = new \stdClass();
            $contact->contact = new \stdClass();
            $contact->contact->number = "K-01012-900";
            $contact->contact->type = "Client";
            $contact->contact->organisation = "examplecompany";
            $body->contacts[] = $contact;

            $response["body"] = $body;
        }

        return $response;
    }

    /**
     * Mock object for tests
     *
     * @param $obj_type
     *
     * @return Object
     */
    public function getMockGetObject($obj_type)
    {
        $config = array(
            "obj_type" => $obj_type
        );

        $apiMock = $this->getMock("\\Salesking\\PHPSDK\\API", array(), array(), '', false);

        return new Object($apiMock, $config);
    }

    /**
     * @covers Collection::load
     */
    public function testLoadReturnsCorrectObjects()
    {
        $this->assertInstanceOf("\\Salesking\\PHPSDK\\Collection", $this->object->load());

        $items = $this->object->getItems();

        $this->assertInstanceOf("\\Salesking\\PHPSDK\\Object", $items[0]);
    }

    /**
     * @covers Collection::getFilters
     */
    public function testGetFiltersReturnsFilterArray()
    {
        $this->object->setFilters(array("q" => "salesking", "number" => "K-123-0001"));
        $this->assertEquals(array("q" => "salesking", "number" => "K-123-0001"), $this->object->getFilters());
    }

    /**
     * @covers Collection::setFilters
     */
    public function testSetFiltersAppliesFilters()
    {
        $this->assertInstanceOf(
            "\\Salesking\\PHPSDK\\Collection",
            $this->object->setFilters(array("q" => "salesking", "number" => "K-123-0001"))
        );

        $this->assertEquals(array("q" => "salesking", "number" => "K-123-0001"), $this->object->getFilters());
    }

    /**
     * @covers   SaleskingCollection::setFilters
     * @expectedException \Salesking\PHPSDK\Exception
     * @expectedExceptionMessage Filter does not exist
     * @expectedExceptionCode    FILTER_NOTEXISTING
     */
    public function testSetFiltersThrowsExceptionOnNonExistingFilter()
    {
        $this->object->setFilters(array("q" => "salesking", "notexisting" => "string"));
    }

    /**
     * @covers Collection::addFilter
     */
    public function testAddFilterAppendsFilterToArray()
    {
        $this->assertInstanceOf("\\Salesking\\PHPSDK\\Collection", $this->object->addFilter("q", "salesking"));
        $this->assertEquals(array("q" => "salesking"), $this->object->getFilters());
    }

    /**
     * @covers Collection::addFilter
     * @expectedException \Salesking\PHPSDK\Exception
     * @expectedExceptionMessage Filter does not exist
     * @expectedExceptionCode    FILTER_NOTEXISTING
     */
    public function testAddFilterThrowsExceptionOnNotExistingFilter()
    {
        $this->object->addFilter("notexisting", "string");
    }

    /**
     * @covers Collection::addFilter
     * @expectedException \Salesking\PHPSDK\Exception
     * @expectedExceptionMessage Invalid filter value
     * @expectedExceptionCode    FILTER_INVALID
     */
    public function testAddFilterThrowsExceptionOnInvalidFilterValue()
    {
        $this->object->addFilter("birthday_to", "string");
    }

    /**
     * @covers Collection::validateFilter
     */
    public function testValidateFilterWorksCorrectly()
    {
        // make sure that not existing properties return false
        $this->assertFalse($this->object->validateFilter("notexisting", "string"));

        // test property format date
        $this->assertFalse($this->object->validateFilter("birthday_to", "1999/01/01"));
        $this->assertFalse($this->object->validateFilter("birthday_to", "string"));
        $this->assertFalse($this->object->validateFilter("birthday_to", "123"));
        $this->assertFalse($this->object->validateFilter("birthday_to", "1999-01-32"));
        $this->assertFalse($this->object->validateFilter("birthday_to", "1999-13-01"));
        $this->assertTrue($this->object->validateFilter("birthday_to", "1999-01-01"));
    }

    /**
     * @covers Collection::getItems
     */
    public function testGetItemsReturnsArrayOfObjects()
    {
        $this->object->load();
        $items = $this->object->getItems();

        $this->assertInstanceOf("\\Salesking\\PHPSDK\\Object", $items[0]);

    }

    /**
     * @covers Collection::getType
     */
    public function testGetTypeReturnsCorrectValue()
    {
        $this->object->setObjType("contact");

        $this->assertEquals("contact", $this->object->getObjType());
    }

    /**
     * @covers Collection::setType
     */
    public function testSetTypeSetsCorrectValue()
    {
        $this->object->setObjType("contact");

        $this->assertEquals("contact", $this->object->getObjType());
    }

    /**
     * @covers Collection::__call
     */
    public function testCallReturnsSelf()
    {
        $this->assertInstanceOf("\\Salesking\\PHPSDK\\Collection", $this->object->q("salesking"));
    }

    /**
     * @covers Collection::__call
     * @expectedException \BadMethodCallException
     */
    public function testCallThrowsExceptionOnNotExistingFilter()
    {
        $this->object->notexisting("string");
    }

    /**
     * @covers Collection::__call
     * @expectedException \Salesking\PHPSDK\Exception
     * @expectedExceptionCode FILTER_INVALID
     * @expectedExceptionMessage Invalid filter value
     */
    public function testCallThrowsExceptionOnInvalidFilterValue()
    {
        $this->object->birthday_to("string");
    }

    /**
     * @covers Collection::sort
     */
    public function testSortReturnsSelf()
    {
        $this->assertInstanceOf("\\Salesking\\PHPSDK\\Collection", $this->object->sort("ASC"));
        $this->assertInstanceOf("\\Salesking\\PHPSDK\\Collection", $this->object->sort("DESC"));
    }

    /**
     * @covers Collection::sort
     * @expectedException \Salesking\PHPSDK\Exception
     * @expectedExceptionMessage Invalid sorting direction - please choose either ASC or DESC
     * @expectedExceptionCode    SORT_INVALIDDIRECTION
     */
    public function testSortThrowsExceptionOnInvalidDirection()
    {
        $this->object->sort("invalid");
    }

    /**
     * @covers Collection::getSort
     */
    public function testGetSortReturnsCorrectSortValue()
    {
        $this->assertEquals("ASC", $this->object->getSort());

        $this->object->sort("DESC");
        $this->assertEquals("DESC", $this->object->getSort());

    }

    /**
     * @covers Collection::sortBy
     */
    public function testSortByReturnsSelf()
    {
        $this->assertInstanceOf("\\Salesking\\PHPSDK\\Collection", $this->object->sortBy("number"));
    }

    /**
     * @covers Collection::sortBy
     * @expectedException \Salesking\PHPSDK\Exception
     * @expectedExceptionMessage Invalid property for sorting
     * @expectedExceptionCode    SORTBY_INVALIDPROPERTY
     */
    public function testSortByThrowsExceptionOnInvalidProperty()
    {
        $this->object->sortBy("invalid");
    }

    /**
     * @covers Collection::getSortBy
     */
    public function testGetSortByReturnsCorrectValue()
    {
        $this->assertEquals("", $this->object->getSortBy());

        $this->object->sortBy("number");
        $this->assertEquals("number", $this->object->getSortBy());
    }

    /**
     * @covers Collection::perPage
     */
    public function testPerPageReturnsSelf()
    {
        $this->assertInstanceOf("\\Salesking\\PHPSDK\\Collection", $this->object->perPage(90));
    }

    /**
     * @covers Collection::perPage
     * @expectedException \Salesking\PHPSDK\Exception
     * @expectedExceptionMessage Please set an integer <100 for the per-page limit
     * @expectedExceptionCode PERPAGE_ONLYINT
     */
    public function testPerPageThrowsExceptionOnIntsLarger100()
    {
        $this->object->perPage(101);
    }

    /**
     * @covers Collection::perPage
     * @expectedException \Salesking\PHPSDK\Exception
     * @expectedExceptionMessage Please set an integer <100 for the per-page limit
     * @expectedExceptionCode PERPAGE_ONLYINT
     */
    public function testPerPageThrowsExceptionOnStrings()
    {
        $this->object->perPage("string");
    }

    /**
     * @covers Collection::getPerPage
     */
    public function testGetPerPageReturnsCorrectPerPageValue()
    {
        $this->assertEquals(100, $this->object->getPerPage());

        $this->object->perPage(99);
        $this->assertEquals(99, $this->object->getPerPage());
    }

    /**
     * @covers Collection::getTotal
     */
    public function testGetTotalReturnsCorrectTotal()
    {
        $this->object->load();
        $this->assertEquals(5, $this->object->getTotal());
    }

    /**
     * @covers Collection::getTotalPages
     */
    public function testGetTotalPagesReturnsCorrectTotalPageCount()
    {
        $this->object->load();
        $this->assertEquals(4, $this->object->getTotalPages());
    }

    /**
     * @covers Collection::getCurrentPage
     */
    public function testGetCurrentPageReturnsCorrectPage()
    {
        $this->object->load();
        $this->assertEquals(1, $this->object->getCurrentPage());
    }
}
