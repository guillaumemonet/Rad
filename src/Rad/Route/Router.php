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

use Rad\Api;
use Rad\Cache\Cache;
use Rad\Errors\Http\NotAcceptableException;
use Rad\Errors\Http\NotFoundException;
use Rad\Log\Log;

/**
 * Description of ApiRoute
 *
 * @author Guillaume Monet
 */
final class Router implements RouterInterface {

    private $path_array = array();

    public function __construct() {
        
    }

    /**
     * 
     * @param Route $route
     * @return \self
     */
    public function addGetRoute(Route $route): self {
        $this->path_array[$route->getVersion()]["GET"][] = $route;
        Log::getHandler()->debug("GET Adding route " . $route->getRegExp());
        return $this;
    }

    /**
     * 
     * @param Route $route
     * @return \self
     */
    public function addPostRoute(Route $route): self {
        $this->path_array[$route->getVersion()]["POST"][] = $route;
        Log::getHandler()->debug("POST Adding route " . $route->getRegExp());
        return $this;
    }

    /**
     * 
     * @param Route $route
     * @return \self
     */
    public function addPutRoute(Route $route): self {
        $this->path_array[$route->getVersion()]["PUT"][] = $route;
        Log::getHandler()->debug("PUT Adding route " . $route->getRegExp());
        return $this;
    }

    /**
     * 
     * @param Route $route
     * @return \self
     */
    public function addPatchRoute(Route $route): self {
        $this->path_array[$route->getVersion()]["PATCH"][] = $route;
        Log::getHandler()->debug("PATCH Adding route " . $route->getRegExp());
        return $this;
    }

    /**
     * 
     * @param Route $route
     * @return \self
     */
    public function addDeleteRoute(Route $route): self {
        $this->path_array[$route->getVersion()]["DELETE"][] = $route;
        Log::getHandler()->debug("DELETE Adding route " . $route->getRegExp());
        return $this;
    }

    /**
     * 
     * @param Route $route
     * @return \self
     */
    public function addOptionsRoute(Route $route): self {
        $this->path_array[$route->getVersion()]["OPTIONS"][] = $route;
        Log::getHandler()->debug("OPTIONS Adding route " . $route->getRegExp());
        return $this;
    }

    /**
     * 
     * @param array $routes
     * @return \self
     */
    public function setRoutes(array $routes): self {
        foreach ($routes as $route) {
            $method = "add" . ucfirst($route->getVerb()) . "Route";
            $this->$method($route);
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
        $this->{$method}($route);
        return $this;
    }

    public function __toString() {
        return print_r($this->path_array, true);
    }

    /**
     * 
     * @param Api $api
     * @throws NotAcceptableException
     * @throws NotFoundException
     */
    public function route(Api &$api) {
        $request = $api->getRequest();
        $response = $api->getResponse();
        $version = $request->version;
        $method = $request->method;
        $path = $request->path;
        //error_log(print_r($this->path_array, true));
        if (isset($this->path_array[$version][$method])) {
            $found = false;
            foreach ($this->path_array[$version][$method] as $route) {
                $regExp = $route->getRegExp();
                Log::getHandler()->debug("preg_match('$regExp','$path')");
                if (preg_match($regExp, $path, $m)) {
                    $found = true;
                    array_shift($m);
                    $api->getRequest()->path_datas = $m;
                    Log::getHandler()->debug($method . " : " . $path . " Matching " . $regExp);
                    $api->getMiddleware()->layer($route->getMiddlewares());
                    $classController = $route->getClassName();
                    $method = $route->getMethodName();
                    $controller = new $classController();
                    $observers = $route->getObservers();
                    array_map(function($observer) use ($controller) {
                        $controller->attach($observer);
                    }, $observers);
                    /* foreach($observers as $observer){
                      $controller->attach($observer);
                      } */

                    $datas = $api->getMiddleware()->call($request, $response, $route, function($request, $response, $route) use($controller, $method) {
                        return $controller->{$method}($request, $response, $route);
                    });
                    $response->setData($datas);
                    break;
                }
            }
            if (!$found) {
                throw new NotFoundException("No route found for " . $path);
            }
        } else {
            throw new NotFoundException("No Method " . $method . " found for " . $path);
        }
    }

    public function save() {
        Cache::getHandler()->set("RadRoute", serialize($this->path_array));
    }

    public function load(): bool {
        $routes = unserialize(Cache::getHandler()->get("RadRoute"));
        if (isset($routes) && $routes != null && sizeof($routes) > 0) {
            $this->path_array = $routes;
            return true;
        } else {
            return false;
        }
    }

}
