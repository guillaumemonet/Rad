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
use Rad\Encryption\Encryption;
use Redis;

/**
 * Description of Redis_Handler
 *
 * @author Guillaume Monet
 */
final class RedisCacheHandler implements CacheInterface {

    /**
     * @var Redis
     */
    private $redis = null;

    public function __construct() {
        $this->redis = new Redis();
        $this->connect(Config::get("cache_redis", "host"), Config::get("cache_redis", "port"));
    }

    public function clear(): bool {
        $this->redis->flushDB();
        return true;
    }

    public function delete($key) {
        return $this->redis->del(Encryption::hashMd5($key));
    }

    public function deleteMultiple($keys): bool {
        $ret = true;
        foreach ($keys as $key) {
            $ret &= $this->delete(Encryption::hashMd5($key));
        }
        return $ret;
    }

    public function get($key, $default = null) {
        return $this->redis->get(Encryption::hashMd5($key));
    }

    public function getMultiple($keys, $default = null) {
        $ret = [];
        foreach ($keys as $k) {
            $ret[$k] = $this->get($k, $default);
        }
        return $ret;
    }

    public function has($key): bool {
        return $this->exists(Encryption::hashMd5($key));
    }

    public function set($key, $value, $ttl = null): bool {
        if ($ttl === null) {
            return $this->set(Encryption::hashMd5($key), $value);
        } else {
            return $this->setex(Encryption::hashMd5($key), $ttl, $value);
        }
    }

    public function setMultiple($values, $ttl = null): bool {
        $ret = true;
        foreach ($values as $key => $value) {
            $ret &= $this->set($key, $value, $ttl);
        }
        return $ret;
    }

}
