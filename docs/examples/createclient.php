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

// create a configuration array
// you have to register your app FIRST
$config = array(
    "sk_url" => "https://YOUR-SUBDOMAIN.dev.salesking.eu",
    "app_url" => "http://localhost:8888/php-sdk/docs/examples/oauth.php",
    "app_id" => "YOUR-APP-ID",
    "app_secret" => "YOUR-APP-ID"
);

// create a new salesking object
$sdk = new Salesking($config);

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