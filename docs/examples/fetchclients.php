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
require_once("app_config.php");

// create a new salesking object
$sdk = new API(sk_app_config());

// fetch a new client object
try {
    $contacts = $sdk->getCollection(array("type" => "contact","autoload" => true));
} catch (Exception $e) {
    // error handling because schema file isn't available
    die("no schema");
}

try {
    $contacts->sort("ASC")->sortby("number")->q("salesking")->load();
} catch (Exception $e) {
    print_r($e->getErrors());
}
