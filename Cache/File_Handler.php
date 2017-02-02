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

/**
 * Description of CacheManager
 *
 * @author Guillaume Monet
 */
final class File_Handler extends ICacheManager {

    public function __construct() {
        
    }

    /**
     * 
     * @param array $keys
     * @return type
     */
    public function read(array $keys) {
        $ret = array();
        foreach ($keys as $k) {
            if (file_exists(Config::get('install', 'path') . Config::get("cache_file", "path") . sha1($k))) {
                $tmp = file_get_contents(Config::get('install', 'path') . Config::get("cache_file", "path") . sha1($k));
                if ($tmp !== false) {
                    $ret[$k] = $tmp;
                }
            }
        }
        return $ret;
    }

    public function write(array $keys, $expire = null) {
        foreach ($keys as $k => $v) {
            if ($v !== null) {
                file_put_contents(Config::get('install', 'path') . Config::get("cache_file", "path") . sha1($k), $v, LOCK_EX);
            }
        }
    }

    public function delete(array $keys) {
        foreach ($keys as $k) {
            @unlink($k);
        }
    }

    public function purge() {
        $t = time();
        if ($handle = opendir(Config::get('install', 'path') . Config::get("cache_file", "path"))) {
            while (false !== ($file = readdir($handle))) {
                if ($file != ".." && $file != "." && @filectime(Config::get('install', 'path') . Config::get("cache_file", "path") . $file) < ($t - (int) Config::get("cache_file", "lifetime"))) {
                    @unlink(Config::get("cache_file", "path") . $file);
                }
            }
        }
    }

}
