<?php

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

namespace Rad\Cache;

use Exception;
use Rad\Config\Config;
use Rad\Log\Log;
use Redis;


/**
 * Description of Redis_Handler
 *
 * @author Guillaume Monet
 */
final class Redis_Handler extends ICacheManager {

    //@var Redis
    private $redis = null;

    public function __construct() {
	$this->redis = new Redis();
	$this->connect(Config::get("cache_redis", "host"), Config::get("cache_redis", "port"));
    }

    public function delete(array $keys) {
	try {
	    foreach ($keys as $k => $v) {
		// deleting the value from redis
		$this->redis->del($k);
	    }
	} catch (Exception $e) {
	    Log::error($e->getMessage());
	}
    }

    public function read(array $keys) {
	$ret = array();
	try {
	    foreach ($keys as $k => $v) {
		$ret[] = $this->redis->get($k);
	    }
	} catch (Exception $e) {
	    Log::error($e->getMessage());
	}
	return $ret;
    }

    public function write(array $keys, $expire = null) {
	try {
	    if ($expire !== null) {
		foreach ($keys as $k => $v) {
		    $this->redis->setex($k, $expire, $v);
		}
	    } else {
		foreach ($keys as $k => $v) {
		    $this->redis->setex($k, $v);
		}
	    }
	} catch (Exception $e) {
	    Log::error($e->getMessage());
	}
    }

    public function purge() {
        
    }

}
