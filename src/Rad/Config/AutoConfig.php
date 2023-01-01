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

namespace Rad\Config;

use Rad\Cache\Cache;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

/**
 * Description of AutoConfig
 *
 * @author guillaume
 */
abstract class AutoConfig {

    private function __construct() {
        
    }

    private function __clone() {
        
    }

    public static function loadControllers($caching = true): array {

        $controllers = unserialize(Cache::getHandler()->get("controllers"));

        if (empty($controllers) || $caching = false) {
            $controllers = self::findControllers();
            Cache::getHandler()->set('controllers', serialize($controllers));
        }
        return $controllers;
    }

    private static function findControllers(): array {
        $controllers = [];
        $installPath = Config::getApiConfig()->install_path;
        $directory   = new RecursiveDirectoryIterator($installPath);
        $iterator    = new RecursiveIteratorIterator($directory);
        $regex       = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
        foreach ($regex as $key => $file) {
            $classname = self::parseFile(current($file));
            if (!empty($classname)) {
                $controllers[] = $classname;
            }
        }
        return $controllers;
    }

    private static function parseFile($file): ?string {
        $content   = file_get_contents($file);
        $matches   = [];
        $classname = [
            'namespace' => '',
            'classname' => ''
        ];

        if (preg_match('/namespace\s+(\w+);/', $content, $matches)) {
            $classname['namespace'] = $matches[1];
        }

        if (preg_match('/class\s+(\w+)\s+extends\s+Controller\s+{/', $content, $matches)) {
            $classname['classname'] = $matches[1];
        }
        if ($classname['classname'] != '') {
            return $classname['namespace'] . "\\" . $classname['classname'];
        } else {
            return null;
        }
    }

}
