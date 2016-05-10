<?php
/**
 * This file brings an example for the Salesking PHP SDK
 * @version     2.0.0
 * @package     SalesKing PHP SDK Examples
 * @license     MIT License; see LICENSE
 * @copyright   Copyright (C) 2012 David Jardin
 * @link        http://www.salesking.eu
 */

use Salesking\PHPSDK\API;
use Salesking\PHPSDK\Exception;

// load library file
require_once "../../vendor/autoload.php";
require_once "app_config.php";

// create a new salesking object
$sdk = new API(sk_app_config());

// fetch a new client object
try {
    $contact = $sdk->getObject("contact");
} catch (\Salesking\PHPSDK\Exception $e) {
    // error handling because schema file isn't available
    die("no schema");
}

// set information on client object
try {
    $contact->type = "Client";
    $contact->organisation = "salesking";
    $contact->last_name= "Max";
    $contact->first_name ="Mustermann";
    $contact->phone_home="123";
} catch (Exception $e) {
    // error handling when setting an undefinied property or wrong value
    die("property error");
}

// execute the request
try {
    $response = $contact->save();
    print_r($response);
} catch (Exception $e) {
    // couldn't save object
    print_r($e);
}
