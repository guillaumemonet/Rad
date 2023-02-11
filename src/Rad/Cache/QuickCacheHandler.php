<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
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
