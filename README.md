
RAD Framework
==========================

[![Latest Stable Version](http://poser.pugx.org/rad/rad-framework/v)](https://packagist.org/packages/rad/rad-framework) 
[![Total Downloads](http://poser.pugx.org/rad/rad-framework/downloads)](https://packagist.org/packages/rad/rad-framework) 
[![Latest Unstable Version](http://poser.pugx.org/rad/rad-framework/v/unstable)](https://packagist.org/packages/rad/rad-framework) 
[![License](http://poser.pugx.org/rad/rad-framework/license)](https://packagist.org/packages/rad/rad-framework) 
[![PHP Version Require](http://poser.pugx.org/rad/rad-framework/require/php)](https://packagist.org/packages/rad/rad-framework)
[![Maintainability](https://api.codeclimate.com/v1/badges/8e095176dd6216eea653/maintainability)](https://codeclimate.com/github/guillaumemonet/Rad/maintainability)

## RAD Framework


RAD (Rapid Application Development) Framework is a lightweight and user-friendly PHP framework designed for quick and efficient web application development. The primary motivation behind creating this framework was to provide a simple and easy-to-use tool for developers who value speed and efficiency.

The goal of RAD Framework is to offer a solid foundation for building web applications while keeping the learning curve minimal. It aims to provide essential features and functionalities commonly required during web development without unnecessary complexities. By focusing on simplicity and flexibility, RAD Framework empowers developers to concentrate on their specific application logic, rather than getting bogged down in intricate framework details.

Developers are encouraged to utilize RAD Framework to expedite their application development process significantly. Whether you are building a small project or a more substantial web application, RAD Framework's modular structure and adherence to PHP standards (such as PSR-3, PSR-4, PSR-7, and others) ensure smooth and maintainable development.

This framework is open-source, meaning you can freely use, modify, and extend it according to your specific project requirements. Feedback and contributions from the community are highly valued, as they help to improve the framework and adapt it to diverse use cases. With RAD Framework, developers have a reliable and lightweight solution to accelerate their web application development, without sacrificing performance or flexibility.


## PSR Standards

The RAD framework is a PHP framework aimed at streamlining web development processes. 

It adheres to various PHP Standards Recommendations (PSRs) to ensure code interoperability and maintainability. 

Here is an overview of the PSRs followed by the RAD framework:

- **PSR-3 Logger Interface:** The framework utilizes PSR-3 for logging, providing a standardized approach for logging messages.
- **PSR-4 Autoloader:** The PSR-4 autoloading standard is employed, allowing efficient class autoloading based on namespaces.
- **PSR-7 Http Message:** RAD leverages the PSR-7 standard, powered by Guzzle HTTP, for handling HTTP messages, providing a consistent interface for interacting with HTTP requests and responses.
- **PSR-11 Container:** The framework employs PSR-11 for dependency injection, enabling the management and retrieval of dependencies through a container.
- **PSR-14 EventDispatcher:** RAD utilizes PSR-14 for event dispatching, facilitating the decoupling of components and promoting the observer pattern.
- **PSR-15 Middleware (Work in Progress):** The framework is in the process of implementing PSR-15, which defines a middleware interface for handling HTTP requests and responses.
- **PSR-16 Caching:** RAD adheres to PSR-16 for caching, allowing developers to implement caching mechanisms efficiently.
- **PSR-17 Http Factory:** The framework incorporates PSR-17, powered by Guzzle HTTP, for creating HTTP request and response objects in a standardized manner.

By following these PSR standards, the RAD framework ensures code consistency, improves code reuse, and promotes collaboration within the PHP development community.

With its focus on performance, optimized object instantiation, simplified dependency injection, the RAD framework provides developers with a robust and efficient environment for building high-performance web applications.


## Installation

To use RAD Framework, you need to have PHP 8.1 or higher and Composer installed on your system.

    Make sure you have PHP 8.1 or a later version installed. You can check your PHP version by running the following command in your terminal:

```bash

php -v
```

If PHP is not installed or you have an older version, you can download and install the latest version from the official PHP website: php.net.

Install Composer if you don't have it already. Composer is a dependency management tool for PHP. You can download and install it by following the instructions on the Composer website: getcomposer.org.

Once PHP and Composer are set up, you can add RAD Framework to your project by including it in the require block of your composer.json file. Add the following line for the stable version:

```json

"require": {
    "rad/rad-framework": "^1.0"
}
```
For the latest development version, you can use the following line:

```json

"require": {
    "rad/rad-framework": "dev-master"
}
```
After adding the line, run the following command to install RAD Framework and its dependencies:

```bash

composer install
```
Composer will fetch the appropriate version of RAD Framework based on the version specified in your composer.json file and set up the necessary files in your project.

With RAD Framework successfully installed, you can now start building your web applications with ease and speed, thanks to its streamlined features and flexible architecture. 

## TODO

* Improve Documentation

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
