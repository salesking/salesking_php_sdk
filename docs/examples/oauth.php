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
    "sk_url" => "https://demo.dev.salesking.eu",
    "app_url" => "http://example.org",
    "app_id" => "dddd3f77ba915b44",
    "app_secret" => "43c3e1cf85eebc28211f34739833591f"
);

// create a new salesking object
$sdk = new Salesking($config);

if(isset($_GET['code'])){
    print_r( $sdk->requestAccessToken($_GET['code']) );
}
else
{
    echo "<a href='".$sdk->requestAuthorizationURL("api/clients offline_access")."'>Grant access</a>";
}