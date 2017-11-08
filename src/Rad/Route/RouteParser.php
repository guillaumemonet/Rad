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
use ReflectionClass;
use ReflectionMethod;

/**
 * Description of RouteParser
 *
 * @author guillaume
 */
abstract class RouteParser {

    private static $allowed_methods = array("get", "post", "put", "patch", "delete", "options", "heads");
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
        Log::getHandler()->debug("Generating Routes");
        $routes = array();
        foreach ($classes as $class) {
            $methods = get_class_methods($class);
            foreach ($methods as $method) {
                $tmp_route = new Route();
                $tmp_route->setClassName($class)
                        ->setMethodName($method);
                $rclass = new ReflectionClass($class);
                $ccomment = $rclass->getDocComment();
                $classAnnotations = self::getInfos($ccomment);
                foreach ($classAnnotations as $key => $annotation) {
                    if ($key == "observer") {
                        $tmp_route->setObservers($annotation);
                    }
                }
                $rmethod = new ReflectionMethod($class, $method);
                $comment = $rmethod->getDocComment();
                $annotations = self::getInfos($comment);
                if (count(array_intersect(self::$allowed_methods, array_keys($annotations))) > 0) {
                    foreach ($annotations as $key => $annotation) {
                        if (in_array(strtolower($key), self::$allowed_methods)) {
                            $tmp_route->setVerb($key)
                                    ->setRegExp($annotation[0]);
                        } else if ($key == self::$middle) {
                            $tmp_route->setMiddlewares($annotation);
                        } else if ($key == self::$version) {
                            $tmp_route->setVersion($annotation[0]);
                        } else if ($key == self::$consume) {
                            $tmp_route->setConsume($annotation[0]);
                        } else if ($key == self::$produce) {
                            $tmp_route->setProduce($annotation[0]);
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
     * @param string $comments
     * @return array
     */
    private static function getInfos(string $comments) {
        $infos = array();
        $array_comments = preg_split("/(\r?\n)/", $comments);
        array_map(function($line) use($infos) {
            // if starts with an asterisk
            if (preg_match('/^(?=\s+?\*[^\/])(.+)/', $line, $matches)) {
                $info = preg_replace('/^(\*\s+?)/', '', trim($matches[1]));
                // if it doesn't start with an "@" symbol
                // then add to the description
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
        }, $array_comments);
        return $infos;
    }

}
