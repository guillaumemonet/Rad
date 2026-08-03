<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Route;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface as PsrMiddlewareInterface;
use Rad\Cache\Cache;
use Rad\Container\ContainerAwareInterface;
use Rad\Error\Http\InternalErrorException;
use Rad\Error\Http\NotFoundException;
use Rad\Log\Log;
use Rad\Middleware\ControllerHandler;
use Rad\Middleware\Dispatcher;
use Rad\Middleware\LegacyMiddlewareAdapter;
use Rad\Middleware\MiddlewareInterface as LegacyMiddlewareInterface;

/**
 * Description of Route
 *
 * @author Guillaume Monet
 */
class Router implements RouterInterface, ContainerAwareInterface {
    /**
     *
     * @var string
     */
    private $cacheName = 'RadRoute';

    /**
     * @var TreeNodeRoute[]
     */
    private $treeRoutes = [];

    private ?ContainerInterface $container = null;

    public function __construct() {

    }

    public function setContainer(ContainerInterface $container): void {
        $this->container = $container;
    }

    /**
     *
     * @param Route $route
     */
    public function addGetRoute(Route $route): self {
        return $this->mapRoute('GET', $route);
    }

    /**
     *
     * @param Route $route
     */
    public function addPostRoute(Route $route): self {
        return $this->mapRoute('POST', $route);
    }

    /**
     *
     * @param Route $route
     */
    public function addPutRoute(Route $route): self {
        return $this->mapRoute('PUT', $route);
    }

    /**
     *
     * @param Route $route
     */
    public function addPatchRoute(Route $route): self {
        return $this->mapRoute('PATCH', $route);
    }

    /**
     *
     * @param Route $route
     */
    public function addDeleteRoute(Route $route): self {
        return $this->mapRoute('DELETE', $route);
    }

    /**
     *
     * @param Route $route
     */
    public function addOptionsRoute(Route $route): self {
        return $this->mapRoute('OPTIONS', $route);
    }

    /**
     *
     * @param array $routes
     */
    public function setRoutes(array $routes): self {
        foreach ($routes as $route) {
            $method = 'add' . ucfirst($route->getMethod()) . 'Route';
            $this->{$method}($route);
        }
        return $this;
    }

    /**
     * @return TreeNodeRoute[]
     */
    public function getRoutes(): array {
        return $this->treeRoutes;
    }

    /**
     *
     * @param string $method
     * @param Route $route
     * @return $this
     */
    public function mapRoute(string $method, Route $route): self {
        if (!isset($this->treeRoutes[$method])) {
            $this->treeRoutes[$method] = new TreeNodeRoute($method);
        }
        $this->treeRoutes[$method]->addFromArray(explode('/', trim($route->getPath(), '/')), $route);
        Log::getHandler()->debug($method . ' Adding route ' . $route->getPath());
        return $this;
    }

    /**
     *
     * @return string
     */
    public function __toString(): string {
        return print_r($this->treeRoutes, true);
    }

    /**
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function route(ServerRequestInterface $request): ResponseInterface {
        $method    = strtoupper($request->getMethod());
        $path      = $request->getUri()->getPath();
        $cacheKey  = $method . 'rt_cache_' . $path;
        $cached    = Cache::getHandler()->get($cacheKey);
        $route     = is_string($cached) && $cached !== '' ? unserialize($cached) : false;
        $nodeRoute = $this->treeRoutes[$method] ?? null;
        if ($nodeRoute != null && $route === false) { //
            $route = $nodeRoute->getRoute(explode('/', trim($path, '/')));
            Cache::getHandler()->set($cacheKey, serialize($route));
        }
        if ($route !== null && $route !== false) {
            $route->setFullPath($path);
            Log::getHandler()->debug($method . ' : ' . $path . ' Matching ' . $route->getPath());
            // Expose the matched route to the PSR-15 pipeline via a request attribute.
            $request    = $request->withAttribute(Route::class, $route);
            $dispatcher = new Dispatcher(
                $this->normalizeMiddlewares($route->getMiddlewares()),
                new ControllerHandler($this->container)
            );
            return $dispatcher->handle($request);
        } else {
            throw new NotFoundException('No Method ' . $method . ' found for ' . $path);
        }
    }

    /**
     * Sort middlewares by priority and adapt any legacy middleware to PSR-15.
     *
     * @param object[] $middlewares
     * @return PsrMiddlewareInterface[]
     * @throws InternalErrorException
     */
    private function normalizeMiddlewares(array $middlewares): array {
        usort($middlewares, static function ($a, $b) {
            $pa = property_exists($a, 'priority') ? $a::$priority : 1;
            $pb = property_exists($b, 'priority') ? $b::$priority : 1;
            return $pa <=> $pb;
        });
        return array_map(static function ($middleware) {
            if ($middleware instanceof PsrMiddlewareInterface) {
                return $middleware;
            }
            if ($middleware instanceof LegacyMiddlewareInterface) {
                return new LegacyMiddlewareAdapter($middleware);
            }
            throw new InternalErrorException(get_class($middleware) . ' is not a valid middleware');
        }, $middlewares);
    }

    /**
     *
     * @return self
     */
    public function save(): self {
        Cache::getHandler()->set($this->cacheName, serialize($this->treeRoutes));
        return $this;
    }

    /**
     * @param string[] $controllers
     * @return self
     */
    public function load(array $controllers): self {
        $cached           = Cache::getHandler()->get($this->cacheName);
        $this->treeRoutes = is_string($cached) && $cached !== '' ? unserialize($cached) : [];
        if (empty($this->treeRoutes)) {
            Log::getHandler()->debug('Generate Tree Route');
            $this->setRoutes(RouteParser::parseRoutes($controllers))
                    ->save();
        }
        return $this;
    }
}
