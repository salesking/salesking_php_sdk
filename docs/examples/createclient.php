<?php
/**
 * This file brings an example for the Salesking PHP SDK
 * @version     1.0.0
 * @package     SalesKing PHP SDK Examples
 * @license     MIT License; see LICENSE
 * @copyright   Copyright (C) 2012 David Jardin
 * @link        http://www.salesking.eu
 */


// load library file
require_once("../../src/salesking.php");
require_once("app_config.php");

// create a new salesking object
$sdk = new Salesking(sk_app_config());

// fetch a new client object
try {
    $client = $sdk->getObject("client");
}
catch(SaleskingException $e) {
    // error handling because schema file isn't available
    die("no schema");
}

// set information on client object
try {
    $client->organisation = "salesking";
    $client->last_name= "Max";
    $client->first_name ="Mustermann";
    $client->phone_home="123";

}

catch (SaleskingException $e)
{
    // error handling when setting an undefinied property or wrong value
    die("property error");
}

// execute the request
try {
    $response = $client->save();
    print_r($response);
}
catch (SaleskingException $e)
{
    // couldn't save object
    print_r($e);
}