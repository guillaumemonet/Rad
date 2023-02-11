<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Annotation;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Read Annotations from PHP classes
 *
 * @author Guillaume Monet
 */
abstract class Annotation {

    private function __construct() {
        
    }

    private function __clone() {
        
    }

    /**
     * 
     * @param string $docblock
     * @return array
     */
    public static function getAnnotations(string $docblock): array {
        $matches     = [];
        preg_match_all('/@(?<name>[A-Za-z_-]+)((?<args>.*))[\r\n]/m', $docblock, $matches, PREG_SET_ORDER);
        $annotations = [];
        array_map(function ($match) use (&$annotations) {
            $annotations[$match['name']][] = trim($match['args']);
        }, $matches);
        return $annotations;
    }

    /**
     * 
     * @param string $class
     * @return array
     */
    public static function getAnnotationsProperties(string $class): array {
        $reflexion   = new ReflectionClass($class);
        $properties  = $reflexion->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);
        $annotations = [];
        array_map(function ($property) use (&$annotations) {
            $annotations[$property->name][] = self::getAnnotations($property->getDocComment());
        }, $properties);
        return $annotations;
    }

    /**
     * 
     * @param string $class
     * @return array
     */
    public static function getAnnotationsClass(string $class): array {
        $reflexion = new ReflectionClass($class);
        $comments  = $reflexion->getDocComment();
        return self::getAnnotations($comments);
    }

    /**
     * 
     * @param string $class
     * @param string $method
     * @return array
     */
    public static function getAnnotationsMethods(string $class, string $method): array {
        $reflexion = new ReflectionMethod($class, $method);
        $comments  = $reflexion->getDocComment();
        return self::getAnnotations($comments);
    }

}
