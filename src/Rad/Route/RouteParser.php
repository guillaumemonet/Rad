<?php

/*
 * The MIT License
 *
 * Copyright 2017 guillaume.
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

use Rad\Controller\Controller;
use Rad\Log\Log;
use ReflectionClass;
use ReflectionMethod;

/**
 * Description of RouteParser
 *
 * @author guillaume
 */
abstract class RouteParser {

    private static $allowed_methods = ['GET', "POST", "PUT", "PATCH", "DELETE"];
    private static $annotationsArray = [
        'middleware' => ['method' => 'setMiddlewares', 'type' => 'array'],
        'api' => ['method' => 'setVersion', 'type' => 'single'],
        'consume' => ['method' => 'setConsume', 'type' => 'array'],
        'produce' => ['method' => 'setProduce', 'type' => 'array'],
        'observer' => ['method' => 'setObservers', 'type' => 'array'],
        'xhr' => ['method' => 'setXhr', 'type' => 'single']
    ];

    private function __construct() {
        
    }

    /**
     * 
     * @param array $classes
     */
    public static function parseRoutes(array $classes) {
        Log::getHandler()->debug('Generating Routes');
        $routes = [];
        array_map(function($class) use(&$routes) {
            Log::getHandler()->debug('Loading Class ' . $class);
            if (is_subclass_of($class, Controller::class)) {
                $routes = array_merge($routes, self::generateRoutes($class));
            } else {
                Log::getHandler()->debug('Not a Controller ' . $class);
            }
        }, $classes);
        return $routes;
    }

    public static function generateRoutes($class) {
        $routes = [];
        $classComments = self::parseClassAnnotations($class);
        $methods = get_class_methods($class);
        array_map(function($method) use (&$routes, $class, $classComments) {
            Log::getHandler()->debug('Loading Method ' . $method);
            $methodComments = self::parseMethodAnnotations($class, $method);
            $paths = self::getPathsFromComment($methodComments);
            array_walk($paths, function($array, $key) use (&$routes, $class, $classComments, $method, $methodComments) {
                foreach ($array as $path) {
                    $route = new Route();
                    $route->setClassName($class)->setMethodName($method)->setMethod($key)->setPath($path);
                    $others = self::getOthersFromComment($methodComments);
                    array_walk($others, function($datas, $key) use ($route) {
                        $method = self::$annotationsArray[$key]['method'];
                        $type = self::$annotationsArray[$key]['type'];
                        $route->{$method}($type === 'array' ? $datas : current($datas));
                    });
                    array_walk($classComments, function($annotation, $key) use ($route) {
                        if (in_array($key, self::$annotationsArray)) {
                            $route->{self::$annotationsArray[$key]}($annotation);
                        }
                    });
                    $routes[] = $route;
                }
            });
        }, $methods);
        return $routes;
    }

    public static function getPathsFromComment($methodComment) {
        return array_filter($methodComment, function($key) {
            return in_array(strtoupper($key), self::$allowed_methods);
        }, ARRAY_FILTER_USE_KEY);
    }

    public static function getOthersFromComment($methodComment) {
        return array_filter($methodComment, function($key) {
            return !in_array(strtoupper($key), self::$allowed_methods) && array_key_exists(strtolower($key), self::$annotationsArray);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     *
     * @param type $class
     * @param type $route
     */
    private static function parseClassAnnotations($class) {
        return self::getAnnotationsArray($class);
    }

    /**
     *
     * @param type $class
     * @param type $method
     * @param type $route
     */
    private static function parseMethodAnnotations($class, $method) {
        return self::getAnnotationsArray($class, $method);
    }

    /**
     * 
     * @param type $class
     * @return type
     */
    private static function getAnnotationsArray($class, $method = null) {
        $reflexion = $method !== null ? new ReflectionMethod($class, $method) : new ReflectionClass($class);
        $comments = $reflexion->getDocComment();
        return self::getInfos($comments);
    }

    /**
     * 
     * @param string $comments
     * @return array
     */
    private static function getInfos(string $comments) {
        $infos = [];
        $array_comments = preg_split("/(\r?\n)/", $comments);
        array_map(function($line) use(&$infos) {
            // if starts with an asterisk
            if (preg_match('/^(?=\s+?\*[^\/])(.+)/', $line, $matches)) {
                $info = preg_replace('/^(\*\s+?)/', '', trim($matches[1]));
                // if it doesn't start with an "@" symbol
                // then add to the description
                if ($info[0] === "@") {
                    // get the name of the param
                    preg_match('/@(\w+)/', $info, $matches);
                    $param_name = $matches[1];
                    // remove the param from the string
                    $value = str_replace("@$param_name ", '', $info);
                    // if the param hasn't been added yet, create a key for it
                    if (!isset($infos[$param_name])) {
                        $infos[$param_name] = [];
                    }
                    // push the param value into place
                    $infos[$param_name][] = $value;
                }
            }
        }, $array_comments);
        return $infos;
    }

}
