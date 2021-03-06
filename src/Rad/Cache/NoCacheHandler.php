<?php

/*
 * The MIT License
 *
 * Copyright 2017 guillaume.
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
 * Description of No_CacheHandler
 *
 * @author guillaume
 */
class NoCacheHandler implements CacheInterface {

    public function clear(): bool {
        return true;
    }

    public function delete($key): bool {
        return false;
    }

    public function deleteMultiple($keys): bool {
        return false;
    }

    public function get($key, $default = null) {
        return $default;
    }

    public function getMultiple($keys, $default = null) {
        return $default;
    }

    public function has($key): bool {
        return false;
    }

    public function set($key, $value, $ttl = null): bool {
        return false;
    }

    public function setMultiple($values, $ttl = null): bool {
        return false;
    }

}
