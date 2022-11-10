<?php

/*
 * The MIT License
 *
 * Copyright 2018 guillaume.
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
    public static function getAnnotationsMethods(string $class, string $method) {
        $reflexion = new ReflectionMethod($class, $method);
        $comments  = $reflexion->getDocComment();
        return self::getAnnotations($comments);
    }

}
