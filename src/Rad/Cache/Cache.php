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

use Psr\SimpleCache\CacheInterface;
use Rad\Config\Config;

/*
 * Description of CacheManager
 *
 * @author Guillaume Monet
 */

final class Cache {

    /**
     * @var CacheInterface
     */
    private static $cache_handler = null;
    private $cache_pool;

    private function __construct() {
        
    }

    private static function init() {
        switch (Config::get("cache", "type")) {
            case "redis":
                self::$cache_handler = new Redis_Handler();
                break;
            case "memcache":
                self::$cache_handler = new Memcache_Handler();
                break;
            case "mysql":
                self::$cache_handler = new Mysql_Handler();
                break;
            case "file":
            default:
                self::$cache_handler = new CacheFile();
        }
    }

    /**
     * Change de default cache handler
     * @param CacheInterface $cache
     */
    public static function setCacheHandler(CacheInterface $cache) {
        self::$cache_handler = $cache;
    }

    /**
     * 
     * @return CacheInterface
     */
    public static function getCacheHandler(): CacheInterface {
        if (self::$cache_handler === null) {
            self::init();
        }
        return self::$cache_handler;
    }

    /**
     * 
     * Return values from cache having keys  
     * @param array $keys
     * @return array
     */
    public static function read(array $keys) {

        $ret = array();
        if ((int) Config::get("cache", "enabled") == 1) {
            if (self::$cache_handler == null) {
                self::init();
            }
            $ret = self::$cache_handler->read($keys);
            return $ret;
        } else {
            return null;
        }
    }

    /**
     * Write array keys=>value in cache
     * @param array $keys
     * @param int $expire
     */
    public static function write(array $keys, $expire = null) {

        if ((int) Config::get("cache", "enabled") == 1) {
            if (self::$cache_handler == null) {
                self::init();
            }
            self::$cache_handler->write($keys, $expire);
        }
    }

    /**
     * Delete keys
     * @param array $keys
     */
    public static function delete(array $keys) {
        if ((int) Config::get("cache", "enabled") == 1) {
            if (self::$cache_handler == null) {
                self::init();
            }
            self::$cache_handler->delete($keys);
        }
    }

    /**
     * Cleanup the cache
     */
    public static function purge() {
        if (Config::get("cache", "enabled") == 1) {
            if (self::$cache_handler == null) {
                self::init();
            }
            self::$cache_handler->purge();
        }
    }

}
