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

namespace Rad\Collection;

use ArrayIterator;

/**
 * Description of Collection
 *
 * @author guillaume
 */
class Collection implements CollectionInterface {

    /**
     * @var array
     */
    protected $data = array();

    /**
     * 
     * @param array $items
     */
    public function __construct(array $items = []) {
        $this->data = clone $items;
    }

    /**
     * 
     * @param string $key
     * @param type $value
     */
    public function set(string $key, $value) {
        $this->data[$key] = $value;
    }

    /**
     * 
     * @param string $key
     * @param type $default
     * @return type
     */
    public function get(string $key, $default = null) {
        return $this->has($key) ? $this->data[$key] : $default;
    }

    /**
     * 
     * @param array $items
     */
    public function replace(array $items) {
        array_map(function ($key, $value) use ($this) {
            $this->set($key, $value);
        }, $items);
    }

    /**
     * 
     * @return array
     */
    public function all(): array {
        return $this->data;
    }

    /**
     * 
     * @return array
     */
    public function keys(): array {
        return array_keys($this->data);
    }

    /**
     * 
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool {
        return array_key_exists($key, $this->data);
    }

    /**
     * 
     * @param string $key
     */
    public function remove(string $key) {
        unset($this->data[$key]);
    }

    /**
     * 
     */
    public function clear() {
        $this->data = array();
    }

    /**
     * 
     * @param string $key
     * @return bool
     */
    public function offsetExists(string $key): bool {
        return $this->has($key);
    }

    /**
     * 
     * @param string $key
     * @return type
     */
    public function offsetGet(string $key) {
        return $this->get($key);
    }

    /**
     * 
     * @param string $key
     * @param type $value
     */
    public function offsetSet(string $key, $value) {
        $this->set($key, $value);
    }

    /**
     * 
     * @param string $key
     */
    public function offsetUnset(string $key) {
        $this->remove($key);
    }

    /**
     * 
     * @return int
     */
    public function count(): int {
        return count($this->data);
    }

    /**
     * 
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator {
        return new ArrayIterator($this->data);
    }

}
