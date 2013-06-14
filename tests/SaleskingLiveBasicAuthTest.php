<?php
/**
 * @version     1.0.0
 * @package     SalesKing PHP SDK Tests
 * @license     MIT License; see LICENSE
 * @copyright   Copyright (C) 2012 David Jardin
 * @link        http://www.salesking.eu
 */

require_once (dirname(__FILE__).'/../src/salesking.php');
/**
 * Test class for Salesking with real connection
 */
class SaleskingLiveBasicAuthTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Salesking
     */
    protected $object;

    /**
     * @var array curl options
     */
    public $curl_options = array(
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_USERAGENT      => 'salesking-sdk-tests-1.0',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLINFO_HEADER_OUT => true
    );

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        // first, lets check weather a livetest_credentials.php exists
        if(!file_exists(dirname(__FILE__)."/test_config.php")) {
            $this->markTestSkipped("No connection credentials provided");
        }

        // if it exists, lets require it and set up our config object
        require_once("test_config.php");
        $config = sk_basic_auth_config();

        // now we set up curl to determine whether we're online or not and we have a valid url
        $curl = curl_init();
        $options = $this->curl_options;
        $options[CURLOPT_URL] = $config['sk_url'];
        curl_setopt_array($curl, $options);
        $result = curl_exec($curl);

        // lets have a look on our result
        if($result === false) {
            // we're not online, so lets close the connection to avoid memory leaks and throw a message
            curl_close($curl);
            $this->markTestSkipped("can't connect to SalesKing server");
        }

        // close the connection
        curl_close($curl);

        // assign our object for the tests
        $this->object = new Salesking($config);
    }


    /**
     * @covers Salesking::request
     */
    public function testRequest()
    {
        // lets create a object which then gets used to do all kinds of requests
        $client = $this->object->getObject("client");
        $client->organisation = "salesking";
        $client->last_name= "Joe";
        $client->first_name ="Example";
        $client->phone_home="123";

        // create a new object
        try {
            $client->save();
        }
        catch (SaleskingException $e) {
            $this->fail("Could not create client object");
        }

        //assert that the client was created successfull and has a valid id now
        $this->assertTrue(22 == strlen($client->id));

        // update an existing object
        $client->gender = "male";

        try {
            $client->save();
        }
        catch (SaleskingException $e) {
            $this->fail("Could not update client object");
        }

        // delete an object
        try {
            $client->delete();
        }
        catch (SaleskingException $e) {
            $this->fail("Could not delete client object");
        }
    }
}
?>