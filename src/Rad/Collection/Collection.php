<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
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
    protected $data = [];

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
        array_map(function ($key, $value) {
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
        $this->data = [];
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

    /**
     * 
     * @param string $key
     * @return bool
     */
    public function offsetExists($key): bool {
        return $this->has($key);
    }

    /**
     * 
     * @param string $key
     * @return type
     */
    public function offsetGet($key) {
        return $this->get($key);
    }

    /**
     * 
     * @param string $key
     * @param type $value
     */
    public function offsetSet($key, $value) {
        $this->set($key, $value);
    }

    /**
     * 
     * @param string $key
     */
    public function offsetUnset($key) {
        $this->remove($key);
    }

}
