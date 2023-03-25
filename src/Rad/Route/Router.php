<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Route;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rad\Cache\Cache;
use Rad\Error\Http\NotFoundException;
use Rad\Http\Response;
use Rad\Log\Log;
use Rad\Middleware\Middleware;

/**
 * Description of Route
 *
 * @author Guillaume Monet
 */
class Router implements RouterInterface {

    /**
     * 
     * @var string
     */
    private $cacheName = "RadRoute";

    /**
     * @var TreeNodeRoute[]
     */
    private $treeRoutes = [];

    public function __construct() {
        
    }

    /**
     * 
     * @param Route $route
     * @return \self
     */
    public function addGetRoute(Route $route): self {
        return $this->mapRoute('GET', $route);
    }

    /**
     * 
     * @param Route $route
     * @return \self
     */
    public function addPostRoute(Route $route): self {
        return $this->mapRoute('POST', $route);
    }

    /**
     * 
     * @param Route $route
     * @return \self
     */
    public function addPutRoute(Route $route): self {
        return $this->mapRoute('PUT', $route);
    }

    /**
     * 
     * @param Route $route
     * @return \self
     */
    public function addPatchRoute(Route $route): self {
        return $this->mapRoute('PATCH', $route);
    }

    /**
     * 
     * @param Route $route
     * @return \self
     */
    public function addDeleteRoute(Route $route): self {
        return $this->mapRoute('DELETE', $route);
    }

    /**
     * 
     * @param Route $route
     * @return \self
     */
    public function addOptionsRoute(Route $route): self {
        return $this->mapRoute('OPTIONS', $route);
    }

    /**
     * 
     * @param array $routes
     * @return \self
     */
    public function setRoutes(array $routes): self {
        foreach ($routes as $route) {
            $method = "add" . ucfirst($route->getMethod()) . "Route";
            $this->{$method}($route);
        }
        return $this;
    }

    /**
     * 
     * @param string $method
     * @param string $path
     * @param Route $route
     * @param int $version
     * @return $this
     */
    public function mapRoute(string $method, Route $route): self {
        if (!isset($this->treeRoutes[$method])) {
            $this->treeRoutes[$method] = new TreeNodeRoute($method);
        }
        $this->treeRoutes[$method]->addFromArray(explode("/", trim($route->getPath(), '/')), $route);
        Log::getHandler()->debug($method . ' Adding route ' . $route->getPath());
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function __toString() {
        return print_r($this->path_array, true);
    }

    /**
     * 
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function route(ServerRequestInterface $request): ResponseInterface {
        $method    = $request->getMethod();
        $path      = $request->getUri()->getPath();
        $route     = unserialize(Cache::getHandler()->get($method . "rt_cache_" . $path));
        $nodeRoute = $this->treeRoutes[strtoupper($method)];
        if ($nodeRoute != null && $route === false) { //
            $route = $nodeRoute->getRoute(explode('/', trim($path, '/')));
            Cache::getHandler()->set($method . "rt_cache_" . $path, serialize($route));
        }
        if ($route !== null && $route !== false) {
            $route->setFullPath($path);
            Log::getHandler()->debug($method . " : " . $path . " Matching " . $route->getPath());
            $middleware       = new Middleware($route->getMiddlewares());
            $classController  = $route->getClassName();
            $methodController = $route->getMethodName();
            $response         = $middleware->call($request, new Response(200), $route,
                    function ($request, $response, $route) use ($classController, $methodController) {
                        $controller = new $classController($route);
                        $route->applyObservers($controller);
                        return $controller->{$methodController}($request, $response, $route->getArgs());
                    }
            );
            return $response;
        } else {
            throw new NotFoundException("No Method " . $method . " found for " . $path);
        }
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
     * 
     * @return bool
     */
    public function load(array $controllers): self {
        $this->treeRoutes = unserialize(Cache::getHandler()->get($this->cacheName));
        if (empty($this->treeRoutes)) {
            Log::getHandler()->debug('Generate Tree Route');
            $this->setRoutes(RouteParser::parseRoutes($controllers))
                    ->save();
        }
        return $this;
    }

}
