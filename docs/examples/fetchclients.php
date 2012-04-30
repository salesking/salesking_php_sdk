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
$config = array(
    "accessToken" => "6b7156f3f6d34c4c0f76fac9996d4511",
    "sk_url" => "https://demo.dev.salesking.eu",
    "app_url" => "http://example.org",
    "app_id" => "dddd3f77ba915b44",
    "app_secret" => "43c3e1cf85eebc28211f34739833591f"
);

// create a new salesking object
$sdk = new Salesking($config);

// fetch a new client object
try {
    $clients = $sdk->getCollection(array("type" => "client","autoload" => true));
}
catch(SaleskingException $e) {
    // error handling because schema file isn't available
    die("no schema");
}

try{
    $clients->sort("ASC")->sortby("number")->q("salesking")->load();
}
catch(SaleskingException $e)
{
    print_r($e->getErrors());
}
?>
