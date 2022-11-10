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

<?php

require(__DIR__ . "/../vendor/autoload.php");

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rad\Api;
use Rad\Controller\Controller;
use Rad\Log\Log;
use Rad\Template\Template;
use Rad\Utils\Time;

/*
 * The MIT License
 *
 * Copyright 2017 guillaume.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Simple example for testing purpose
 *
 * @author guillaume
 * @Controller
 */
class Example extends Controller {

    public $state = 1;

    /**
     * @get /
     * @produce html
     */
    public function html(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write("<b>Hello World</b>");
        return $response;
    }

    /**
     * @get /json/
     * @produce json
     */
    public function json(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response  = $response->withAddedHeader('Hello', 'Moto');
        $std       = new stdClass();
        $std->toto = "toto/fdsf   sdf://";
        $std->arr  = ["toto ", "titi"];
        $response->getBody()->write(json_encode([$std, $std]));
        return $response;
    }

    /**
     * @api 1
     * @get /helloworld/(?<name>[aA-zZ]*)/display/(?<welcome>.*)/
     * @produce html
     */
    public function htmlWithArgs(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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

    /**
     * @get /template/
     * @produce html
     */
    public function template(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response = $response->withAddedHeader('Hello', 'Moto');
        if (!Template::getHandler()->isCached("index.tpl", "cached", "compiled")) {
            Log::getHandler()->debug("Not Cached index.tpl");
            Template::getHandler()->assign("index", "RAD");
        }
        $html = Template::getHandler()->fetch("index.tpl", "cached", "compiled");
        $response->getBody()->write($html);
        return $response;
    }

    /**
     * @get /observer/
     * @produce html
     * @observer \TestObserver
     */
    public function observer(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write("State Change");
        $this->state = 2;
        $this->notify();
        return $response;
    }

    /**
     * @get /test/large/(?<name>[aA-zZ]*)/one/
     * @produce html
     */
    public function pathOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write("Path One");
        return $response;
    }

    /**
     * @get /test/large/(?<name>[aA-zZ]*)/two/
     * @produce html
     */
    public function pathTwo(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write("Path Two");
        return $response;
    }

}

$time = Time::startCounter();
/**
 * Load TestObserver class
 */
require(__DIR__ . '/TestObserver.php');

$app = new Api(__DIR__ . "/config/");

$app->addControllers([
    Example::class
])->run(function () {
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
* [psr-14](http://www.php-fig.org/psr/psr-14/) EventDispatcher (WIP Remplace Observer Pattern)
* [psr-15](http://www.php-fig.org/psr/psr-15/) Middleware (WIP)
* [psr-16](http://www.php-fig.org/psr/psr-16/) Caching
* [psr-17](http://www.php-fig.org/psr/psr-17/) Http Factory (Thanks to Guzzle Http)



