<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Config;

use Rad\Cache\Cache;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use ReflectionClass;
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

        $controllers = unserialize(Cache::getHandler()->get('controllers'));

        if (empty($controllers) || $caching = false) {
            $controllers = self::findControllers();
            Cache::getHandler()->set('controllers', serialize($controllers));
        }
        return $controllers;
    }

    private static function findControllers(): array {
        $controllers = [];
        $installPath = Config::getApiConfig()->install_path . Config::getApiConfig()->controllers_path;
        $directory   = new RecursiveDirectoryIterator($installPath);
        $iterator    = new RecursiveIteratorIterator($directory);
        $regex       = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
        foreach ($regex as $file) {
            $classname = self::parseFile(current($file));
            if (!empty($classname)) {
                $controllers[] = $classname;
            }
        }
        return $controllers;
    }

    private static function parseFile($file): ?string {
        $content    = file_get_contents($file);
        $namespaces = [];
        $classnames = [];
        preg_match('/namespace\s+(.*);/', $content, $namespaces);
        preg_match('/class\s+(\w+)\s+/', $content, $classnames);
        if (count($namespaces) == 0 && count($classnames) == 0) {
            error_log("Not a class " . $file);
            return null;
        }

        try {
            $clname    = $namespaces[1] . '\\' . $classnames[1];
            $reflector = new ReflectionClass($clname);
            if ($reflector->isSubclassOf('Rad\\Controller\\Controller')) {
                return $clname;
            } else {
                return null;
            }
        } catch (Exception $ex) {
            return null;
        }
    }

}
