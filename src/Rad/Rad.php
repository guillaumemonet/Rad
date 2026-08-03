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
use Psr\Http\Message\ServerRequestInterface;
use Rad\Config\Config;
use Rad\Container\Container;
use Rad\Container\ContainerAwareInterface;
use Rad\Container\ServiceProvider;
use Rad\Error\Http\NotFoundException;
use Rad\Http\Response;
use Rad\Http\ServerRequest;
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
        $routerClass        = Config::getApiConfig('router');
        $serverRequestClass = Config::getApiConfig('serverrequest');
        $this->router       = $routerClass !== null ? new $routerClass() : new Router();
        $this->request      = $serverRequestClass !== null ? $serverRequestClass::fromGlobals() : ServerRequest::fromGlobals();
        $this->container    = $this->bootContainer();
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
            if ($response instanceof Response) {
                $response->send();
            }
        } catch (ErrorException $ex) {
            Log::getHandler()->error($ex->getMessage());
            if ($errorClosure !== null) {
                call_user_func_array($errorClosure, [$ex]);
            } else {
                $response = new Response($ex->getCode());
                $response->getBody()->write($ex->getCode() . ' ' . $ex->getMessage());
                $response->send();
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
