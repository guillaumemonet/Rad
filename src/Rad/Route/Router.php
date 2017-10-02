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

use ErrorException;
use Rad\Api;
use Rad\Cache\Cache;
use Rad\Errors\Http\NotAcceptableException;
use Rad\Errors\Http\NotFoundException;
use Rad\Log\Log;
use Rad\Utils\Mime;

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
     * @param string $path
     * @param string $function
     * @throws ErrorException
     */
    public function get(string $path, Route $route, $version = 1) {
        if (!isset($this->path_array[$version]["GET"][$path])) {
            $this->path_array[$version]["GET"][$path] = $route;
            Log::getLogHandler()->debug("GET Adding route " . $path);
            return $this;
        } else {
            throw new ErrorException("GET Path [$path] Already exist");
        }
    }

    /**
     * 
     * @param string $path
     * @param string $function
     * @throws ErrorException
     */
    public function post(string $path, Route $route, $version = 1) {
        if (!isset($this->path_array[$version]["POST"][$path])) {
            $this->path_array[$version]["POST"][$path] = $route;
            Log::getLogHandler()->debug("POST Adding route " . $path);
            return $this;
        } else {
            throw new ErrorException("POST Path [$path] Already exist");
        }
    }

    /**
     * 
     * @param string $path
     * @param string $function
     * @throws ErrorException
     */
    public function options(string $path, Route $route, $version = 1) {
        if (!isset($this->path_array[$version]["OPTIONS"][$path])) {
            $this->path_array[$version]["OPTIONS"][$path] = $route;
            Log::getLogHandler()->debug("OPTIONS Adding route " . $path);
            return $this;
        } else {
            throw new ErrorException("OPTIONS Path [$path] Already exist");
        }
    }

    /**
     * 
     * @param string $path
     * @param string $function
     * @throws ErrorException
     */
    public function put(string $path, Route $route, $version = 1) {
        if (!isset($this->path_array[$version]["PUT"][$path])) {
            $this->path_array[$version]["PUT"][$path] = $route;
            Log::getLogHandler()->debug("PUT Adding route " . $path);
            return $this;
        } else {
            throw new ErrorException("PUT Path [$path] Already exist");
        }
    }

    /**
     * 
     * @param string $path
     * @param string $function
     * @throws ErrorException
     */
    public function patch(string $path, Route $route, $version = 1) {
        if (!isset($this->path_array[$version]["PATCH"][$path])) {
            $this->path_array[$version]["PATCH"][$path] = $route;
            Log::getLogHandler()->debug("PATCH Adding route " . $path);
            return $this;
        } else {
            throw new ErrorException("PATCH Path [$path] Already exist");
        }
    }

    /**
     * 
     * @param string $path
     * @param string $function
     * @throws ErrorException
     */
    public function delete(string $path, Route $route, $version = 1) {
        if (!isset($this->path_array[$version]["DELETE"][$path])) {
            $this->path_array[$version]["DELETE"][$path] = $route;
            Log::getLogHandler()->debug("DELETE Adding route " . $path);
            return $this;
        } else {
            throw new ErrorException("DELETE Path [$path] Already exist");
        }
    }

    public function set(array $routes) {
        foreach ($routes as $route) {
            //$this->path_array[$route->version] = $routes;
            $this->{$route->verb}($route->regex, $route, $route->version);
        }
        Log::getLogHandler()->debug(print_r($this->path_array, true));
    }

    /**
     * 
     * @param string $method
     * @param string $path
     * @param Route $route
     * @param int $version
     * @return $this
     */
    public function map(string $method, string $path, Route $route, $version = 1) {
        $this->{$method}($path, $route, $version);
        return $this;
    }

    public function __toString() {
        return print_r($this->path_array, true);
    }

    /**
     * 
     * @param type $version
     * @param type $method
     * @param type $path
     * @param type $request
     * @param type $response
     * @param type $middle
     * @return type
     * @throws NotFoundException
     */
    public function route(Api &$api) {
        $version = $api->getRequest()->version;
        $method = $api->getRequest()->method;
        $path = $api->getRequest()->path;

        if (isset($this->path_array[$version][$method])) {
            $found = false;
            foreach ($this->path_array[$version][$method] as $reg_path => $route) {
                $p = "/^" . str_replace("/", "\/", (trim($reg_path, "/"))) . "$/";
                if (preg_match($p, $path, $m)) {
                    $found = true;
                    array_shift($m);
                    $api->getRequest()->path_datas = $m;
                    Log::getLogHandler()->debug($method . " : " . $path . " Matching " . $p . " Consume " . $route->consume);
                    if ($route->consume == null || $route->consume == "" || in_array($api->getRequest()->getHeader("CONTENT_TYPE"), Mime::MIME_TYPES[$route->consume])) {
                        $controller = new $route->className();
                        $datas = $controller->{$route->methodName}($api);
                        if (isset($route->produce)) {
                            $api->getResponse()->setDataType($route->produce);
                        }
                        $api->getResponse()->setData($datas);
                    } else {
                        throw new NotAcceptableException("Wrong Content Type");
                    }
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
        Cache::write(array("RadRoute" => serialize($this->path_array)));
    }

    public function load(): bool {
        $routes = unserialize(Cache::read(array("RadRoute"))["RadRoute"]);
        if (isset($routes) && $routes != null && sizeof($routes) > 0) {
            $this->path_array = $routes;
            return true;
        } else {
            return false;
        }
    }

    public function clean() {
        Cache::delete(array("RadRoute"));
    }

}
