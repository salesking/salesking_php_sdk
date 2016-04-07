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
    $contacts = $sdk->getCollection(array("obj_type" => "contact","autoload" => true));
}
catch(SaleskingException $e) {
    // error handling because schema file isn't available
    die("no schema");
}

try{
  $contacts->sort("ASC")->sortby("number")->q("salesking")->load();
}
catch(SaleskingException $e)
{
    print_r($e->getErrors());
}
?>
