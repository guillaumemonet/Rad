<?php

/*
 * The MIT License
 *
 * Copyright 2017 Guillaume Monet.
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

namespace Rad\Route;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rad\Cache\Cache;
use Rad\Codec\Codec;
use Rad\Error\Http\NotFoundException;
use Rad\Http\Response;
use Rad\Log\Log;
use Rad\Middleware\Middleware;
use Rad\Session\Session;

/**
 * Description of Route
 *
 * @author Guillaume Monet
 */
class Router implements RouterInterface {

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
    public function mapRoute(string $method, Route $route) {
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
        $route     = null;
        $nodeRoute = $this->treeRoutes[strtoupper($method)];
        if ($nodeRoute != null) {
            $route = $nodeRoute->getRoute(explode('/', trim($path, '/')));
        }
        if ($route !== null) {
            $route->setFullPath($path);
            Log::getHandler()->debug($method . " : " . $path . " Matching " . $route->getPath());
            $middleware       = new Middleware($route->getMiddlewares());
            $classController  = $route->getClassName();
            $methodController = $route->getMethodName();
            $route->isSessionEnabled() ? Session::getHandler()->start() : '';
            $response         = $middleware->call($request, new Response(200), $route, function ($request, $response, $route) use ($classController, $methodController) {
                $controller = new $classController($request, $response, $route);
                $route->applyObservers($controller);
                $datas      = $controller->{$methodController}($request, $response, $route->getArgs());
                $response   = $controller->getResponse();
                $response->getBody()->write(Codec::getHandler(current($route->getProcucedMimeType()))->serialize($datas));
                return $response;
            });
            $route->isSessionEnabled() ? Session::getHandler()->end() : '';
            return $response;
        } else {
            throw new NotFoundException("No Method " . $method . " found for " . $path);
        }
    }

    /**
     * 
     */
    public function save() {
        $cacheName = "RadRoute";
        Cache::getHandler()->set($cacheName, serialize($this->treeRoutes));
    }

    /**
     * 
     * @return bool
     */
    public function load(): bool {
        $cacheName = "RadRoute";
        $routes    = unserialize(Cache::getHandler()->get($cacheName));
        if (isset($routes) && $routes != null && sizeof($routes) > 0) {
            $this->treeRoutes = $routes;
            return true;
        } else {
            return false;
        }
    }

}
