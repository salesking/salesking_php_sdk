# SalesKing PHP SDK
[![Build Status](https://secure.travis-ci.org/salesking/salesking_php_sdk.png)](http://travis-ci.org/salesking/salesking_php_sdk)

Automate your workflow's by integrating and connecting your business with SalesKing.
This PHP Software-Development-Kit provides solid and handy tools for building
SalesKing App's

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

## Examples

Run doc/examples/* AFTER registering an app on our *free developer* machine at: 

[dev.salesking.eu/signup](https://www.dev.salesking.eu/signup/dev-gh)

To run them in your checkout directory, use the shiny new PHP build-in webserver.

    cd salesking_php_sdk/docs/examples
    php -S localhost:8000

[Tutorial: Run a PHP server in any folder on Ubuntu](http://dev.blog.salesking.eu/coding/run-php-webserver-in-any-directory-on-ubuntu/)

Feel free to help us improve the demo code.

## Tests

Run the test with PHPUnit:

    phpunit tests

No PHP Unit? [See install Guide for Ubuntu](http://dev.blog.salesking.eu/coding/how-to-run-phpunit-tests-on-ubuntu/)

We also have test running against a real SalesKing account. Those are skipped if no test_config.php file is found


Copyright (c) 2012 David Jardin, released under the MIT license