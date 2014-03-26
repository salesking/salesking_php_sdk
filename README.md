# SalesKing PHP SDK
[![Build Status](https://secure.travis-ci.org/salesking/salesking_php_sdk.png)](http://travis-ci.org/salesking/salesking_php_sdk)

Automate your workflow's by integrating and connecting your business with SalesKing.
This PHP Software-Development-Kit provides solid and handy tools for building
SalesKing App's

## Examples

Run doc/examples/* AFTER registering an app on our *free developer* machine at: 

[dev.salesking.eu/signup](https://www.dev.salesking.eu/signup/dev-gh)

To run them in your checkout directory, use the shiny new PHP build-in webserver.

    cd salesking_php_sdk/docs/examples
    php -S localhost:8000

[Tutorial: Run a PHP server in any folder on Ubuntu](http://dev.blog.salesking.eu/coding/run-php-webserver-in-any-directory-on-ubuntu/)

Feel free to help us improve the demo code.

## Example code

Those examples use http basic auth. Please add your login credentials to the config array.

Create a new client in SalesKing:
```php
    $config = array( "sk_url" => "https://MY-SUBDOMAIN.salesking.eu",
                     "user" => "my-salesking@login-email.eu",
                     "password" => 'yourPass' );
    $sdk = new Salesking($config);
    $client = $sdk->getObject("client");
    $client->organisation = "salesking";
    $response = $client->save();
```
Get a list of clients
```php
    $sdk = new Salesking($config);
    $clients = $sdk->getCollection(array("type" => "client","autoload" => true));
    $clients->sort("ASC")->sortby("number")->q("salesking")->load();
```

More examples e.g. on how to create documents e.g.

https://github.com/salesking/salesking_php_sdk/blob/master/tests/SaleskingLiveInvoiceTest.php#L74


## Login / Authentication

The SDK supports login by HTTP Basic Auth(user email+password) or oAuth2.

### HTTP Basic Auth

Basic Auth is the quickest way to get started, but it's a security risks! If someone grabs your login he can do
whatever you can! PLEASE use a separate API User and, in production environments, reduce his rights with
SalesKing's Role-System. Also note that a user session expires if he logs-in twice (Web+Api or two browsers)

It's ok to use this method in private one-to-one integrations, where you are the only user talking to your own SalesKing
account.

### oAuth2

oAuth should be used for web-services allowing users to connect a SalesKing account and if you aim for higher
security. With oAuth it's the app that talks to a user account with reduced rights. Users have to interact by
granting an app access and can revoke apps any time, just like Facebook or Twitter apps.

Developers need to register an app to get oauth credentials(key+secret). Apps are initally only visible to the creator
and if you know the app url. If you have a great app please contact the SalesKing team to relase it for all users.

## Tests

Run all tests with PHPUnit:

    phpunit tests

No PHP Unit? [See install Guide for Ubuntu](http://dev.blog.salesking.eu/coding/how-to-run-phpunit-tests-on-ubuntu/)

Run a single testfile

    phpunit --colors tests/SaleskingCollectionTest.php

Run a group(or single method) of tests (see @group markup in each test-function comments)

    phpunit --colors --group live-invoice tests


### Test against a real SalesKing account.

Copy and edit tests/test_config.php.default
Those live tests are skipped if no tests/test_config.php file is found or if the login data is invalid.


Copyright (c) 2012 David Jardin, released under the MIT license
