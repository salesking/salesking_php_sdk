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
class SaleskingLiveInvoiceTest extends PHPUnit_Framework_TestCase
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
   * Creates a client + order, copies the order to an invoice and opens the invoices
   * @group live-invoice
   */
  public function testCreateInvoiceFromOrder()
  {
    // lets create a client
    $client = $this->object->getObject("contact");
    $client->type = "Client";
    $client->organisation = "salesking";
    $client->last_name= "Joe";
    $client->first_name ="Orderexample";
    $client->phone_home="123";

    // create a client object object
    try {
      $client->save();
    }
    catch (SaleskingException $e) {
      $this->fail("Could not create contact object");
    }

    // a line item
    $item = $this->object->getObject('line_item');
    $item->position = 1;
    $item->name = "Stuff";
    $item->quantity = 1;

    // and an order
    $order = $this->object->getObject('order');
    $order->contact_id = $client->id;
    $order->line_items = array($item->getData());
    $order->status = "open";

    try{
      $order->save();
    }
    catch (SaleskingException $e) {
      $this->fail("Could not create order object");
    }

    // create invoice
    $response = $this->object->request('/api/invoices','POST',
      json_encode(array(
        "source" => $order->id
      ))
    );

    if($response['code'] != 201) {
      throw new SaleskingException("CREATEINVOICEERROR", "Could not create invoice");
    }

    $invoice = $this->object->getObject("invoice");
    $invoice->load($response['body']->invoice->id);

    // change invoice number and status
    $invoice->status = "closed";
    $invoice->number = "99999";

    try{
      $invoice->save();
    }
    catch (SaleskingException $e) {
      $this->fail("Could not change invoice number");
    }
    // revert added data
    try{
      $invoice->status = "draft";
      $invoice->save();

      $order->status = "draft";
      $order->save();

      $invoice->delete();
      $order->delete();
      $client->delete();
    }
    catch (SaleskingException $e) {
      $this->fail("Could not delete objects");
    }

  }
}
?>
