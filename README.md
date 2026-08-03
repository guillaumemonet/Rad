
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
- **PSR-15 Middleware:** Requests flow through a PSR-15 pipeline (`Rad\Middleware\Dispatcher`) of `Psr\Http\Server\MiddlewareInterface` layers ending in the controller dispatcher. Legacy Rad middlewares are adapted transparently.
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

## Development / QA

Install the dev dependencies then use the Composer scripts:

```bash
composer install

composer test        # PHPUnit
composer phpstan     # Static analysis
composer cs-check    # Coding standards (dry-run)
composer cs-fix      # Coding standards (apply)
composer qa          # cs-check + phpstan + test
```

No local PHP? A Docker dev stack is provided:

```bash
docker compose build
docker compose run --rm php composer install
docker compose run --rm php composer qa     # phpstan + phpunit
```

CI (GitHub Actions) runs the same checks on PHP 8.2, 8.3 and 8.4.

PHPStan runs at level 5; existing legacy findings are frozen in
`phpstan-baseline.neon` so only new issues fail the build. Coding-standards
(`composer cs-fix`) are not yet applied to the legacy code, so that CI step is
informational for now.

For an existing project with legacy type debt, generate a PHPStan baseline first:

```bash
vendor/bin/phpstan analyse --generate-baseline
```

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

Routes are declared with PHP 8 attributes (namespace `Rad\Route\Attribute`) :

```php

<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rad\Route\Attribute\Get;
use Rad\Route\Attribute\Produce;

class Exemple extends \Rad\Controller\Controller {

    #[Get('/'), Produce('html')]
    public function html(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write("<b>Hello World</b>");
        return $response;
    }

}
```

Available attributes :

| Attribute | Target | Example |
|-----------|--------|---------|
| `#[Get]` `#[Post]` `#[Put]` `#[Patch]` `#[Delete]` `#[Options]` | method (repeatable) | `#[Get('/users/(?<id>\d+)/')]` |
| `#[Produce]` / `#[Consume]` | method / class | `#[Produce('json', 'html')]` |
| `#[Middleware]` / `#[Security]` | method / class (repeatable) | `#[Middleware(MyMiddleware::class)]` |
| `#[Version]` | method / class | `#[Version(1)]` |
| `#[Session]` `#[Xhr]` `#[Cacheable]` `#[EnableOptions]` | method / class | `#[Session]` |
| `#[Cors]` | method / class | `#[Cors('https://example.com')]` |
| `#[AllowHeaders]` / `#[ExposeHeaders]` | method / class | `#[AllowHeaders('X-Total-Count')]` |

> Legacy `@get` / `@produce` docblock annotations are still parsed automatically for controllers that have not been migrated yet.

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


## Dependency Injection

RAD ships a lightweight PSR-11 container with constructor autowiring
(`Rad\Container\Container`). Controllers are resolved through it, so you can
type-hint services directly in a controller constructor:

```php
use Rad\Container\Container;

class UserController extends \Rad\Controller\Controller {

    public function __construct(\Rad\Route\Route $route = null, private ?UserRepository $users = null) {
        parent::__construct($route);
    }
}
```

Register bindings on the application container:

```php
$app = new \Rad\Rad(__DIR__ . '/config/');

$container = $app->getContainer();
$container->bind(UserRepositoryInterface::class, MysqlUserRepository::class);
$container->singleton(Clock::class, fn() => new SystemClock());
$container->instance(SomeService::class, $alreadyBuilt);
```

- `get($id)` resolves with singleton semantics (built once, then cached).
- `make($id, ['param' => $value])` returns a fresh instance, overriding
  constructor arguments by name (this is how the current `Route` is injected
  into controllers).
- `call($callable, $params)` invokes any callable with autowired arguments.

The current request (`ServerRequestInterface`), router (`RouterInterface`) and
the container itself are pre-registered and injectable out of the box.

The legacy service facades are also bridged into the container, so their
interfaces can be type-hinted directly (each resolves to the handler configured
for that service, exactly like calling the facade):

```php
class ReportController extends \Rad\Controller\Controller {

    public function __construct(
        \Rad\Route\Route $route = null,
        private ?\Psr\Log\LoggerInterface $log = null,          // Log::getHandler()
        private ?\Rad\Cache\CacheInterface $cache = null,       // Cache::getHandler()
        private ?\Rad\Database\DatabaseAdapter $db = null       // Database::getHandler()
    ) {
        parent::__construct($route);
    }
}
```

Bridged interfaces: `Psr\Log\LoggerInterface`, `Rad\Cache\CacheInterface`,
`Rad\Database\DatabaseAdapter`, `Rad\Session\SessionInterface`,
`Rad\Cookie\CookieInterface`, `Rad\Encryption\EncryptionInterface`,
`Rad\Template\TemplateInterface`, `Rad\Language\LanguageInterface`,
`Rad\Codec\CodecInterface`, `Rad\Build\BuildInterface`,
`Rad\ClientApi\ClientApiInterface`, `Rad\Mail\MailInterface` and
`Psr\EventDispatcher\EventDispatcherInterface` (when configured).

## Middleware (PSR-15)

Write a standard PSR-15 middleware and attach it to a route/controller with the
`#[Middleware(...)]` attribute:

```php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Timing implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $start = microtime(true);
        return $handler->handle($request)
            ->withHeader('X-Elapsed-Ms', (string) round((microtime(true) - $start) * 1000, 2));
    }
}
```

```php
use Rad\Route\Attribute\Get;
use Rad\Route\Attribute\Middleware;

#[Get('/'), Middleware(Timing::class)]
public function index(...): ResponseInterface { /* ... */ }
```

The matched `Route` is available on the request as an attribute
(`$request->getAttribute(\Rad\Route\Route::class)`). Middlewares can be ordered
by declaring `public static int $priority` (lower runs first). Middlewares still
written against the old `Rad\Middleware\MiddlewareInterface` keep working through
an adapter.

## Events (PSR-14)

The legacy Observer/Observable classes have been removed in favour of a PSR-14
event dispatcher (`Rad\Event\EventHandler`). Define events by extending
`Rad\Event\AbstractEvent` (which supports `stopPropagation()`), and listeners by
implementing `Rad\Event\EventListenerInterface`:

```php
use Rad\Event\AbstractEvent;
use Rad\Event\EventListenerInterface;

final class UserRegistered extends AbstractEvent {
    public function __construct(public readonly int $userId) {}
}

final class SendWelcomeEmail implements EventListenerInterface {
    public function handle(object $event): void {
        if ($event instanceof UserRegistered) { /* ... */ }
    }
}
```

Register listeners and dispatch:

```php
$dispatcher = \Rad\Event\Event::getHandler();          // PSR-14 EventDispatcherInterface
if ($dispatcher instanceof \Rad\Event\EventHandler) {
    $dispatcher->addListener(UserRegistered::class, new SendWelcomeEmail());
}

// From anywhere (a controller can also use $this->dispatch($event)):
$dispatcher->dispatch(new UserRegistered(42));
```

The dispatcher is also injectable as `Psr\EventDispatcher\EventDispatcherInterface`.

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
* [psr-14](http://www.php-fig.org/psr/psr-14/) EventDispatcher (replaces the old Observer pattern)
* [psr-15](http://www.php-fig.org/psr/psr-15/) Middleware (Dispatcher + RequestHandler)
* [psr-16](http://www.php-fig.org/psr/psr-16/) Caching
* [psr-17](http://www.php-fig.org/psr/psr-17/) Http Factory (Thanks to Guzzle Http)
