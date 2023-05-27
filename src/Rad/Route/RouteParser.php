<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Route;

use Psr\Http\Message\ResponseInterface;
use Rad\Annotation\Annotation;
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

    private static $allowed_methods  = ['GET', "POST", "PUT", "PATCH", "DELETE", "OPTIONS"];
    private static $annotationsArray = [
        'middleware' => ['method' => 'addMiddlewares', 'type' => 'array'],
        'api'        => ['method' => 'setVersion', 'type' => 'single'],
        'consume'    => ['method' => 'setConsume', 'type' => 'array'],
        'produce'    => ['method' => 'setProduce', 'type' => 'array'],
        'observer'   => ['method' => 'setObservers', 'type' => 'array'],
        'xhr'        => ['method' => 'setXhr', 'type' => 'single'],
        'session'    => ['method' => 'enableSession', 'type' => 'single'],
        'cors'       => ['method' => 'enableCors', 'type' => 'single'],
        'options'    => ['method' => 'enableOptions', 'type' => 'single'],
        'cachable'   => ['method' => 'enableCache', 'type' => 'single'],
        'security'   => ['method' => 'enableSecurity', 'type' => 'array'],
        'aheaders'   => ['method' => 'allowHeaders', 'type' => 'array'],
        'xheaders'   => ['method' => 'exposeHeaders', 'type' => 'array']
    ];

    private function __construct() {
        
    }

    private function __clone() {
        
    }

    /**
     * 
     * @param array $classes
     */
    public static function parseRoutes(array $classes) {
        Log::getHandler()->debug('Generating Routes');
        $routes = [];
        array_map(function ($class) use (&$routes) {
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
        $routes        = [];
        $classComments = self::parseClassAnnotations($class);
        //Cleaning non controller methods
        $methods       = array_filter(get_class_methods($class), function ($method) use ($class) {
            Log::getHandler()->debug('Loading Method ' . $method . ' ' . (new ReflectionMethod($class, $method))->getReturnType());
            return ((new ReflectionMethod($class, $method))->getReturnType() == ResponseInterface::class);
        });
        array_walk($methods, function ($method) use (&$routes, $class, $classComments) {
            $methodComments = self::parseMethodAnnotations($class, $method);
            $paths          = self::getPathsFromComment($methodComments);
            array_walk($paths, function ($array, $action) use (&$routes, $class, $classComments, $method, $methodComments) {
                array_walk($array, function ($path) use (&$routes, $action, $class, $classComments, $method, $methodComments) {
                    $route    = new Route();
                    $route->setClassName($class)->setMethodName($method)->setMethod($action)->setPath($path);
                    self::enableFunctions($route, $action);
                    $others   = array_merge(self::getOthersFromComment($methodComments), self::getOthersFromComment($classComments));
                    self::enableOtherFunctions($others, $route);
                    $routes[] = $route;
                });
            });
        });
        return $routes;
    }

    private static function enableFunctions(&$route, $function, $datas = []) {
        $f = strtolower($function);
        if (isset(self::$annotationsArray[strtolower($f)])) {
            $method = self::$annotationsArray[$f]['method'];
            $type   = self::$annotationsArray[$f]['type'];
            $route->{$method}($type === 'array' ? $datas : current($datas));
            Log::getHandler()->debug("Enable function $f -> $method");
        }
    }

    private static function enableOtherFunctions($others, &$route) {
        array_walk($others, function ($datas, $key) use ($route) {
            self::enableFunctions($route, $key, $datas);
        });
    }

    private static function getPathsFromComment($methodComment) {
        return array_filter($methodComment, function ($key) {
            return in_array(strtoupper($key), self::$allowed_methods);
        }, ARRAY_FILTER_USE_KEY);
    }

    private static function getOthersFromComment($methodComment) {
        return array_filter($methodComment, function ($key) {
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
        $comments  = $reflexion->getDocComment();
        return Annotation::getAnnotations($comments);
    }

}
