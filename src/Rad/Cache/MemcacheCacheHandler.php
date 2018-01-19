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

use Memcached;
use Psr\SimpleCache\CacheInterface;
use Rad\Config\Config;

/**
 * Description of CacheManager
 *
 * @author Guillaume Monet
 */
final class MemcacheCacheHandler implements CacheInterface {

    private $memcache = null;
    private $defaultTTL = null;

    public function __construct() {
        $config = Config::getServiceConfig('cache', 'memcache');
        $this->memcache = new Memcached();
        $this->memcache->addServer($config->url, (int) $config->port, 100);
        $this->defaultTTL = $config->lifetime;
    }

    /**
     * Read values for a set of keys from cache
     * @param array $keys list of keys to fetch
     * @return array list of values with the given keys used as indexes
     * @return boolean true on success, false on failure
     */
    public function read(array $keys) {
        $res = $this->memcache->getMulti($keys);
        $_res = [];
        if (is_array($res) && sizeof($res) > 0) {
            foreach ($res as $k => $v) {
                $_res[$k] = $v;
            }
        }
        return $_res;
    }

    /**
     * Save values for a set of keys to cache
     * @param array $keys list of values to save
     * @param int $expire expiration time
     * @return boolean true on success, false on failure
     */
    public function write(array $keys, $expire = null) {
        
    }

    /**
     * Display only stats usage
     */
    public function getStats() {
        return print_r($this->memcache->getextendedstats(), true);
    }

    /**
     * 
     * @return bool
     */
    public function clear(): bool {
        return $this->memcache->flush();
    }

    /**
     * Remove values from cache
     * @param array $keys list of keys to delete
     * @return boolean true on success, false on failure
     */
    public function delete($key) {
        
    }

    public function deleteMultiple($keys): bool {
        $ret = false;
        foreach ($keys as $k) {
            $ret &= $this->memcache->delete($k);
        }
        return $ret;
    }

    public function get($key, $default = null) {
        
    }

    public function getMultiple($keys, $default = null) {
        
    }

    public function has($key): bool {
        
    }

    public function set($key, $value, $ttl = null): bool {
        if ($ttl == null) {
            $ttl = (int) $this->defaultTTL;
        }
        return $this->memcache->set($key, $value, $ttl);
    }

    public function setMultiple($values, $ttl = null): bool {
        if ($ttl == null) {
            $ttl = (int) $this->defaultTTL;
        }
        return $this->memcache->setMulti($values, $ttl);
    }

}
