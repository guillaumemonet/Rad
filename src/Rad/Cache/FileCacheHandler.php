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

/**
 * FileCacheManager
 * Use files to cache datas
 *
 * @author Guillaume Monet
 */
class FileCacheHandler implements CacheInterface {

    private $path       = null;
    private $defaultTTL = null;

    public function __construct() {
        $config           = Config::getServiceConfig('cache', 'file')->config;
        $this->path       = $config->path;
        $this->defaultTTL = $config->lifetime;
    }

    /**
     * 
     * @param type $key
     * @return bool
     */
    public function delete($key): bool {
        $md5 = Encryption::hashMd5($key);
        return unlink($this->path . $md5);
    }

    /**
     * 
     * @param type $keys
     * @return bool
     */
    public function deleteMultiple($keys): bool {
        $ret = false;
        foreach ($keys as $k) {
            $ret &= $this->delete($k);
        }
        return $ret;
    }

    /**
     * 
     * @param type $key
     * @param type $default
     * @return type
     */
    public function get($key, $default = null) {
        $md5 = Encryption::hashMd5($key);
        if (file_exists($this->path . $md5) && ($tmp = file_get_contents($this->path . $md5)) !== false) {
            return $tmp;
        } else {
            return $default;
        }
    }

    /**
     * 
     * @param type $keys
     * @param type $default
     * @return array
     */
    public function getMultiple($keys, $default = null): array {
        $ret = [];
        foreach ($keys as $k) {
            $ret[$k] = $this->get($k, $default);
        }
        return $ret;
    }

    /**
     * 
     * @param type $key
     * @return bool
     */
    public function has($key): bool {
        return file_exists($this->path . Encryption::hashMd5($key));
    }

    /**
     * 
     * @param type $key
     * @param type $value
     * @param type $ttl
     * @return bool
     */
    public function set($key, $value, $ttl = null): bool {
        return file_put_contents($this->path . Encryption::hashMd5($key), $value, LOCK_EX);
    }

    /**
     * 
     * @param type $values
     * @param type $ttl
     * @return bool
     */
    public function setMultiple($values, $ttl = null): bool {
        $ret = false;
        foreach ($values as $k => $v) {
            $ret &= $this->set($k, $v, $ttl);
        }
        return $ret;
    }

    /**
     * 
     * @return bool
     */
    public function clear(): bool {
        $t      = time();
        $handle = opendir($this->path);
        while ($handle !== false && false !== ($file   = readdir($handle))) {
            if ($file != ".." && $file != "." && @filectime($this->path . $file) < ($t - (int) $this->defaultTTL)) {
                @unlink($this->path . $file);
            }
        }
    }

    /**
     * 
     * @return bool
     */
    public function purge(): bool {
        $handle = opendir($this->path);
        while ($handle !== false && false !== ($file   = readdir($handle))) {
            if ($file != ".." && $file != ".") {
                @unlink($this->path . $file);
            }
        }
    }

}
