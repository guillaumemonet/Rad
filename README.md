
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
## TODO

* Documentation

## Usage

Init New Rad Object :

```php

<?php

require(__DIR__ . "/../vendor/autoload.php");

$app = new \Rad\Rad(__DIR__ . "/config/");

...
```


Create new Controller :

```php

<?php

class Exemple extends \Rad\Controller\Controller {
    
    /**
     * @get /
     * @produce html
     */
    public function html(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write("<b>Hello World</b>");
        return $response;
    }

}
```

Add controller to the Rad API :

```php

$app->addControllers([
    Example:class
]);
```

Run the Rad API :

```php

$app->run();
```

You can add Closure to the run method :

```php

$app->run(function(){
	echo "End";
});
```


## How is works

* **Config**

* **Middleware**

* **Route**

* **Controller**

## PSR Support

* [psr-3](http://www.php-fig.org/psr/psr-3/) Logger Interface
* [psr-4](http://www.php-fig.org/psr/psr-4/) Autoloader
* [psr-7](http://www.php-fig.org/psr/psr-7/) Http Message (Thanks to Guzzle Http)
* [psr-11](http://www.php-fig.org/psr/psr-11/) Container
* [psr-14](http://www.php-fig.org/psr/psr-14/) EventDispatcher (WIP Remplace Observer Pattern)
* [psr-15](http://www.php-fig.org/psr/psr-15/) Middleware (WIP)
* [psr-16](http://www.php-fig.org/psr/psr-16/) Caching
* [psr-17](http://www.php-fig.org/psr/psr-17/) Http Factory (Thanks to Guzzle Http)
