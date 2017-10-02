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

use Rad\Log\Log;
use ReflectionMethod;

/**
 * Description of RouteParser
 *
 * @author guillaume
 */
class RouteParser {

    private static $allowed_methods = array("get", "post", "put", "patch", "delete", "options");
    private static $middle = "middle";
    private static $version = "api";
    private static $consume = "consume";
    private static $produce = "produce";

    private function __construct() {
        
    }

    /**
     * 
     * @param array $classes
     */
    public static function parseRoutes(array $classes) {
        $routes = array();
        foreach ($classes as $class) {
            $methods = get_class_methods($class);
            foreach ($methods as $method) {
                $tmp_route = new Route();
                $tmp_route->className = $class;
                $tmp_route->methodName = $method;
                $staticMethod = new ReflectionMethod($class, $method);
                $comment = $staticMethod->getDocComment();
                $annotations = self::getInfos($comment);
                if (count(array_intersect(self::$allowed_methods, array_keys($annotations))) > 0) {
                    foreach ($annotations as $key => $annotation) {
                        if (in_array($key, self::$allowed_methods)) {
                            $tmp_route->verb = $key;
                            $tmp_route->regex = $annotation[0];
                        } else if ($key == self::$middle) {
                            $tmp_route->middlewares = $annotation;
                        } else if ($key == self::$version) {
                            $tmp_route->version = $annotation[0];
                        } else if ($key == self::$consume) {
                            $tmp_route->consume = $annotation[0];
                        } else if ($key == self::$produce) {
                            $tmp_route->produce = $annotation[0];
                        }
                    }
                    $routes[] = $tmp_route;
                }
            }
        }
        return $routes;
    }

    /**
     * 
     * @param string $comment
     * @return type
     */
    private static function getInfos(string $comment) {
        $infos = array();
        foreach (preg_split("/(\r?\n)/", $comment) as $line) {
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
                        $infos[$param_name] = array();
                    }
                    // push the param value into place
                    $infos[$param_name][] = $value;
                }
            }
        }
        return $infos;
    }

}
