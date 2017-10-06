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

namespace Rad\Cache;

use ErrorException;
use Psr\SimpleCache\CacheInterface;
use Rad\Config\Config;

/*
 * Description of CacheManager
 *
 * @author Guillaume Monet
 */

class CacheHandler {

    /**
     * @var array of CacheInterface
     */
    private static $cacheHandlers = array();

    private function __construct() {
        
    }

    /**
     * add cache handler
     * @param string $shortName
     * @param CacheInterface $cache
     */
    public static function addHandler(string $shortName, CacheInterface $cache) {
        self::$cacheHandlers[$shortName] = $cache;
    }

    /**
     * 
     * @return CacheInterface
     */
    public static function getHandler(string $handlerType = null): CacheInterface {
        if ($handlerType === null) {
            $handlerType = (string) Config::get("cache", "type");
        }
        if (self::$cacheHandlers[$handlerType] !== null) {
            try {
                $className = ucfirst($handlerType) . "_CacheHandler";
                self::$cacheHandlers[$handlerType] = new $className();
            } catch (ErrorException $ex) {
                throw new ErrorException($handlerType . " Cache Handler not found");
            }
        } else {
            throw new ErrorException("No " . $handlerType . " Cache Handler defined");
        }
        return self::$cacheHandlers[$handlerType];
    }

}
