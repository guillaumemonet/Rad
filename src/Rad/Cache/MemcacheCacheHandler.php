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

    /**
     * 
     * @var Memcached
     */
    private $memcache   = null;
    private $defaultTTL = null;

    public function __construct() {
        $config           = Config::getServiceConfig('cache', 'memcache')->config;
        $this->memcache   = new Memcached();
        $this->memcache->addServer($config->url, (int) $config->port, 100);
        $this->defaultTTL = $config->lifetime;
    }

    /**
     * 
     * @return bool
     */
    public function clear(): bool {
        return $this->memcache->flush();
    }

    /**
     * 
     * @param type $key
     */
    public function delete($key) {
        $this->memcache->delete(Encryption::hashMd5($key));
    }

    /**
     * 
     * @param type $keys
     * @return bool
     */
    public function deleteMultiple($keys): bool {
        $keys = array_flip($keys);
        array_walk($keys, function (&$value, $key) {
            $value = Encryption::hashMd5($key);
        });
        $this->memcache->deleteMulti($keys);
        return true;
    }

    /**
     * 
     * @param type $key
     * @param type $default
     * @return type
     */
    public function get($key, $default = null) {
        return $this->memcache->get(Encryption::hashMd5($key));
    }

    /**
     * 
     * @param type $keys
     * @param type $default
     * @return type
     */
    public function getMultiple($keys, $default = null) {
        $keys = array_flip($keys);
        array_walk($keys, function (&$value, $key) {
            $value = Encryption::hashMd5($key);
        });
        return $this->memcache->getMulti($keys);
    }

    /**
     * 
     * @param type $key
     * @return bool
     */
    public function has($key): bool {
        return !empty($this->get($key));
    }

    /**
     * 
     * @param type $key
     * @param type $value
     * @param type $ttl
     * @return bool
     */
    public function set($key, $value, $ttl = null): bool {
        if ($ttl == null) {
            $ttl = (int) $this->defaultTTL;
        }
        return $this->memcache->set(Encryption::hashMd5($key), $value, $ttl);
    }

    /**
     * 
     * @param type $values
     * @param type $ttl
     * @return bool
     */
    public function setMultiple($values, $ttl = null): bool {
        if ($ttl == null) {
            $ttl = (int) $this->defaultTTL;
        }
        array_walk($values, function (&$value, &$key) {
            $key = Encryption::hashMd5($key);
        });
        return $this->memcache->setMulti($values, $ttl);
    }

}
