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
