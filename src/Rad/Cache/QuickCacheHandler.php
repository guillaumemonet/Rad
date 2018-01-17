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

/**
 * Volatile Cache 
 * To Share Var Between Function
 */
class QuickCacheHandler implements CacheInterface {

    /**
     *
     * @var array
     */
    private $datas = [];

    public function clear(): bool {
        $this->datas = [];
    }

    public function delete($key): bool {
        unset($this->datas["key_" . $key]);
        return false;
    }

    public function deleteMultiple($keys): bool {
        array_diff_key($this->datas, array_flip($keys));
        return true;
    }

    public function get($key, $default = null) {
        if (isset($this->datas["key_" . $key])) {
            return $this->datas["key_" . $key];
        } else {
            return $default;
        }
    }

    public function getMultiple($keys, $default = null): array {
        $ret = [];
        foreach ($keys as $key) {
            $ret[$key] = $this->get($key, $default);
        }
        return $ret;
    }

    public function has($key): bool {
        if (isset($this->datas["key_" . $key])) {
            return true;
        } else {
            return false;
        }
    }

    public function set($key, $value, $ttl = null): bool {
        $this->datas["key_" . $key] = $value;
        return true;
    }

    public function setMultiple($values, $ttl = null): bool {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        return true;
    }

}
