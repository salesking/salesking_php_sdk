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
    //"accessToken" => "added by sdk after oauth succeeded",
    "sk_url" => "https://YOUR-SUBDOMAIN.dev.salesking.eu",
    "app_url" => "http://localhost:8888/php-sdk/docs/examples/oauth.php",
    "app_id" => "YOUR-APP-ID",
    "app_secret" => "YOUR-APP-SECRET"
);

// create a new salesking object
$sdk = new Salesking($config);

if(isset($_GET['code'])){
    print_r( $sdk->requestAccessToken($_GET['code']) );
}
else
{
    echo "<a href='".$sdk->requestAuthorizationURL("api/clients api/invoices offline_access")."'>Grant access</a>";
}