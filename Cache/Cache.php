<?php

namespace Rad\Cache;

use Rad\Config\Config;

/*
 * Copyright (C) 2016 Guillaume Monet
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version. 
*
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/*
 * Description of CacheManager
 *
 * @author Guillaume Monet
 */

final class Cache {

    private static $cache_handler = null;

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
                self::$cache_handler = new File_Handler();
        }
    }

    /**
     * 
     * Return values from cache having keys  
     * @param array $keys
     * @return array
     */
    public static function read(array $keys) {
        $ret = array();
        if (Config::get("cache", "enabled") == 1) {
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
        if (Config::get("cache", "enabled") == 1) {
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
        if (Config::get("cache", "enabled") == 1) {
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
