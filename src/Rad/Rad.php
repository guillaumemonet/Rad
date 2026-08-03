<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad;

use Closure;
use ErrorException;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Rad\Config\Config;
use Rad\Container\Container;
use Rad\Container\ContainerAwareInterface;
use Rad\Container\ServiceProvider;
use Rad\Error\Http\NotFoundException;
use Rad\Http\CurlClient;
use Rad\Http\Emitter;
use Rad\Http\HttpFactory;
use Rad\Http\ServerRequestFactory;
use Rad\Log\Log;
use Rad\Route\Router;
use Rad\Route\RouterInterface;

/**
 *
 */
class Rad {
    public const VERSION = '1.0';

    /**
     *
     * @var Container
     */
    protected Container $container;

    /**
     *
     * @var RouterInterface
     */
    protected ?RouterInterface $router = null;

    /**
     *
     * @var ServerRequestInterface
     */
    protected ?ServerRequestInterface $request = null;

    /**
     *
     * @var string[]
     */
    private array $controllers = [];

    /**
     *
     */
    public function __construct(string $configFilename = null) {
        Config::load($configFilename);
        $routerClass     = Config::getApiConfig('router');
        $this->router    = $routerClass !== null ? new $routerClass() : new Router();
        $this->request   = ServerRequestFactory::fromGlobals();
        $this->container = $this->bootContainer();
    }

    /**
     * Build the application container and register the core services.
     */
    protected function bootContainer(): Container {
        $container = new Container();
        $container->instance(Container::class, $container);
        $container->instance(ContainerInterface::class, $container);
        $container->instance(ServerRequestInterface::class, $this->request);
        $container->instance(RouterInterface::class, $this->router);

        // PSR-17 factories (single implementation behind every factory interface).
        $factory = new HttpFactory();
        $container->instance(HttpFactory::class, $factory);
        $container->instance(ResponseFactoryInterface::class, $factory);
        $container->instance(ServerRequestFactoryInterface::class, $factory);
        $container->instance(StreamFactoryInterface::class, $factory);
        $container->instance(UriFactoryInterface::class, $factory);
        $container->instance(RequestFactoryInterface::class, $factory);
        $container->instance(UploadedFileFactoryInterface::class, $factory);
        $container->instance(Emitter::class, new Emitter());
        // PSR-18 HTTP client.
        $container->singleton(ClientInterface::class, CurlClient::class);

        // Bridge the legacy service facades (Log, Cache, Database, ...).
        ServiceProvider::register($container);
        if ($this->router instanceof ContainerAwareInterface) {
            $this->router->setContainer($container);
        }
        return $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface {
        return $this->container;
    }

    /**
     *
     * @param Closure $finalClosure
     */
    final public function run(Closure $finalClosure = null, Closure $errorClosure = null): void {
        try {
            $response = $this->getRouter()
                    ->load($this->controllers)
                    ->route($this->request);
            $this->container->get(Emitter::class)->emit($response);
        } catch (ErrorException $ex) {
            Log::getHandler()->error($ex->getMessage());
            if ($errorClosure !== null) {
                call_user_func_array($errorClosure, [$ex]);
            } else {
                $factory  = $this->container->get(ResponseFactoryInterface::class);
                $response = $factory->createResponse($ex->getCode() ?: 500);
                $response->getBody()->write($ex->getCode() . ' ' . $ex->getMessage());
                $this->container->get(Emitter::class)->emit($response);
            }
        } finally {
            if ($finalClosure != null) {
                call_user_func_array($finalClosure, []);
            }
        }
    }

    /**
     *
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface {
        if ($this->router !== null) {
            return $this->router;
        } else {
            throw new NotFoundException('RouterInterface Not Defined');
        }
    }

    /**
     *
     * @param RouterInterface $routeur
     */
    public function setRouter(RouterInterface $routeur): self {
        $this->router = $routeur;
        $this->container->instance(RouterInterface::class, $routeur);
        if ($routeur instanceof ContainerAwareInterface) {
            $routeur->setContainer($this->container);
        }
        return $this;
    }

    /**
     *
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface {
        return $this->request;
    }

    /**
     *
     * @param string[] $controllers
     * @return $this
     */
    public function addControllers(array $controllers): self {
        $this->controllers += $controllers;
        return $this;
    }

    /**
     *
     * @param string[] $controllers
     * @return $this
     */
    public function setControllers(array $controllers): self {
        $this->controllers = $controllers;
        return $this;
    }

    /**
     *
     * @param string $controller
     * @return $this
     */
    public function addController(string $controller): self {
        $this->controllers[] = $controller;
        return $this;
    }

    /**
     *
     * @return string[]
     */
    public function getControllers(): array {
        return $this->controllers;
    }
}
