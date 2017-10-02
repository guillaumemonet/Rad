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

use Exception;
use Psr\SimpleCache\CacheInterface;
use Rad\Config\Config;
use Rad\Log\Log;
use Redis;

/**
 * Description of Redis_Handler
 *
 * @author Guillaume Monet
 */
final class Redis_Handler implements CacheInterface {

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
            Log::getLogHandler()->error($e->getMessage());
        }
    }

    public function read(array $keys) {
        $ret = array();
        try {
            foreach ($keys as $k => $v) {
                $ret[] = $this->redis->get($k);
            }
        } catch (Exception $e) {
            Log::getLogHandler()->error($e->getMessage());
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
            Log::getLogHandler()->error($e->getMessage());
        }
    }

    public function clear(): bool {
        $this->redis->flushAll();
    }

    public function deleteMultiple($keys): bool {
        
    }

    public function get($key, $default = null) {
        
    }

    public function getMultiple($keys, $default = null) {
        
    }

    public function has($key): bool {
        
    }

    public function set($key, $value, $ttl = null): bool {
        
    }

    public function setMultiple($values, $ttl = null): bool {
        
    }

}
