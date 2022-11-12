<?php

namespace Rad\Template;

use Rad\Cache\Cache;
use Smarty_CacheResource_KeyValueStore;

/*
 * The MIT License
 *
 * Copyright 2022 Guillaume Monet.
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

class SmartyTemplateCacheHandler extends Smarty_CacheResource_KeyValueStore {

    private $cacheType = null;

    public function __construct($cacheType) {
        $this->cacheType = $cacheType;
    }

    /**
     * 
     * @param array $keys
     * @return array
     */
    public function read(array $keys): array {
        return Cache::getHandler($this->cacheType)->getMultiple($keys);
    }

    public function write(array $keys, $expire = null) {
        Cache::getHandler($this->cacheType)->setMultiple($keys, $expire);
    }

    public function delete(array $keys) {
        Cache::getHandler($this->cacheType)->deleteMultiple($keys);
    }

    public function purge() {
        Cache::getHandler($this->cacheType)->clear();
    }

}
