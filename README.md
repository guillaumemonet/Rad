RAD Framework
==========================

[![Latest Stable Version](https://poser.pugx.org/rad/rad-framework/v/stable)](https://packagist.org/packages/rad/rad-framework)
[![Total Downloads](https://poser.pugx.org/rad/rad-framework/downloads)](https://packagist.org/packages/rad/rad-framework)
[![Latest Unstable Version](https://poser.pugx.org/rad/rad-framework/v/unstable)](https://packagist.org/packages/rad/rad-framework)
[![License](https://poser.pugx.org/rad/rad-framework/license)](https://packagist.org/packages/rad/rad-framework)
[![Maintainability](https://api.codeclimate.com/v1/badges/8e095176dd6216eea653/maintainability)](https://codeclimate.com/github/guillaumemonet/Rad/maintainability)

## What is RAD?
RAD Framework is for Rapid Application Development Framework.

## Why create this?
I wanted to make a basic framework to learn and keep it as simple as possible.

Feel free to use it.

Any advice is welcome.

## Installation

[PHP](https://php.net) 7.1+ and [Composer](https://getcomposer.org) are required.

To get the latest version of RAD Framework, simply add the following line to the require block of your `composer.json` file:

```
"rad/rad-framework": "dev-master"
```

## Usage

```php

require(__DIR__ . "/../vendor/autoload.php");

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rad\Api;
use Rad\Controller\Controller;
use Rad\Log\Log;
use Rad\Utils\Time;

/**
 * Simple example for testing purpose
 *
 * @author guillaume
 * @RestController
 */
class Example extends Controller {

    /**
     * @get /
     * @produce html
     */
    public function helloWorld(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write("<b>Hello World</b>");
        return $response;
    }

    /**
     * @get /json/
     * @produce json
     */
    public function jsonHelloWorld(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response = $response->withAddedHeader('Hello', 'Moto');
        $std            = new stdClass();
        $std->toto      = "toto/fdsf   sdf://";
        $std->arr       = ["toto ", "titi"];
        $response->getBody()->write(json_encode([$std, $std]));
        return $response;
    }

    /**
     * @api 1
     * @get /helloworld/(?<name>[aA-zZ]*)/display/(?<welcome>.*)/
     * @produce html
     */
    public function namedHelloWorld(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write('<b>Hello World</b> ' . $args['name'] . " to " . $args['welcome']);
        return $response;
    }

    /**
     * @api 1
     * @get /server/
     * @cors
     * @produce json
     */
    public function serverRequest(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write(json_encode($request->getHeaders()));
        return $response;
    }

}

$time = Time::startCounter();
$app  = new Api();
$app->addControllers([MyController::class])
        ->run(function () {
            $ltime = Time::endCounter();
            Log::getHandler()->debug("API REQUEST [" . round($ltime, 10) * 1000 . "] ms");
        });

```

## How is works


## PSR Support

* [psr-3](http://www.php-fig.org/psr/psr-3/) Logger Interface
* [psr-4](http://www.php-fig.org/psr/psr-4/) Autoloader
* [psr-7](http://www.php-fig.org/psr/psr-7/) Http Message (Thanks to Guzzle Http)
* [psr-11](http://www.php-fig.org/psr/psr-11/) Container
* [psr-16](http://www.php-fig.org/psr/psr-16/) Caching
* [psr-17](http://www.php-fig.org/psr/psr-17/) Http Factory (Thanks to Guzzle Http)



