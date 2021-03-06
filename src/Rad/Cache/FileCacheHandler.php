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
 * Description of CacheManager
 *
 * @author Guillaume Monet
 */
class FileCacheHandler implements CacheInterface {

    private $path = null;
    private $defaultTTL = null;

    public function __construct() {
        $config = Config::getServiceConfig('cache', 'file')->config;
        $this->path = $config->path;
        $this->defaultTTL = $config->lifetime;
    }

    public function delete($key): bool {
        return unlink($key);
    }

    public function deleteMultiple($keys): bool {
        $ret = false;
        foreach ($keys as $k) {
            $ret &= unlink($k);
        }
        return $ret;
    }

    public function get($key, $default = null) {
        if (file_exists($this->path . Encryption::hashMd5($key))) {
            $tmp = file_get_contents($this->path . Encryption::hashMd5($key));
            if ($tmp !== false) {
                return $tmp;
            }
        }
        return $default;
    }

    public function getMultiple($keys, $default = null) {
        $ret = [];
        foreach ($keys as $k) {
            if (file_exists($this->path . Encryption::hashMd5($k))) {
                $tmp = file_get_contents($this->path . Encryption::hashMd5($k));
                $ret[$k] = $tmp !== false ? $tmp : $default;
            }
        }
        return $ret;
    }

    public function has($key): bool {
        return file_exists($this->path . Encryption::hashMd5($key));
    }

    public function set($key, $value, $ttl = null): bool {
        return file_put_contents($this->path . Encryption::hashMd5($key), $value, LOCK_EX);
    }

    public function setMultiple($values, $ttl = null): bool {
        $ret = false;
        foreach ($values as $k => $v) {
            $ret &= file_put_contents($this->path . Encryption::hashMd5($k), $v, LOCK_EX);
        }
        return $ret;
    }

    public function clear(): bool {
        $t = time();
        $handle = opendir($this->path);
        while ($handle !== false && false !== ($file = readdir($handle))) {
            if ($file != ".." && $file != "." && @filectime($this->path . $file) < ($t - (int) $this->defaultTTL)) {
                @unlink($this->path . $file);
            }
        }
    }

}
