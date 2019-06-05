# php-electrum-api - Electrum library
```
Licence: GPL-3.0
Author: Pascal Krason <p.krason@padr.io>
Language: PHP 5.6-7.1
```
Please note, this library is by far not completed and but can be used in production. Until now i only implemented the most commonly used API-Calls. If you think im missing something, just create an issue or fork the project.

# Setting up Electrum
First you need to setup a new Electrum wallet. Follow the instructions according to your OS at the [Electrum Download Page](https://electrum.org/#download). After the successfull installation you need to set a rpcport by typing:
```
electrum setconfig rpcport 7777
electrum setconfig rpcuser "username"
electrum setconfig rpcpassword "password"
```
Then we can create a default wallet, dont forget to note your generated seed, it's nescessary if you want to recover it one day:
```
electrum create
```
Now we can go ahead and start Electrum in daemon mode:
```
electrum daemon start
```
Since some new version electrum wants you to load your wallet by hand on startup:
```
electrum daemon load_wallet
```

# Requirements
On the PHP side there are not much requirements, you only need at least PHP 5.6 and the curl-Extension installed. Then you can go ahead ans it through [Composer](http://getcomposer.org) which will do everything else for you.

# Install
First you need to install [Composer](https://getcomposer.org/doc/00-intro.md), after you accomplished this you can go ahead:
```
composer require padrio/php-electrum-api
```
Then you can simply include the autoloader and begin using the library:
```php
// Include composer autoloader
require_once 'vendor/autoloader.php';
```

# Examples

## Basic example
A very basic useage example. Every API-Call has it's own request-object. You simply create one and execute it.
```php

$method = new \Electrum\Request\Method\Version();

try {
    $response = $method->execute();
} catch(\Exception $exception) {
    die($exception->getMessage());
}

$response->getVersion();
```

## Custom Client Configuration
Every Request/Method takes a `Electrum\Client`-instance as parameter which replaces the default one. A custom instance can be usefull if you want to set custom config params like another Hostname or Port.
```php
$client = new \Electrum\Client('http://127.0.0.1', 7777, 0, 'username', 'password');
$method = new \Electrum\Request\Method\Version($client);

try {
    $response = $method->execute();
} catch (\Exception $exception) {
    die($exception->getMessage());
}

$response->getVersion();
```

## Advanced exception handling
Dealing with exceptions is easy. You can catch two types of exceptions which indicates whether it's an Request or Response fault.
```php
$method = new \Electrum\Request\Method\Version();

try {
    $response = $method->execute();
} catch (\Electrum\Request\Exception\BadRequestException $exception) {
    die(sprintf(
        'Failed to send request: %s',
        $exception->getMessage()
    ));
} catch(\Electrum\Response\Exception\BadResponseException $exception) {
    die(sprintf(
        'Electrum-Client failed to respond correctly: %s',
        $exception->getMessage()
    ));
}
```
