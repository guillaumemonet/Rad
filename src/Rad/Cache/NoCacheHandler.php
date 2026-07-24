<?php

declare(strict_types=1);

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Cache;

use DateInterval;

/**
 * Description of No_CacheHandler
 *
 * @author guillaume
 */
class NoCacheHandler extends AbstractCacheHandler {

    public function clear(): bool {
        return true;
    }

    public function purge(): bool {
        return true;
    }

    public function delete(string $key): bool {
        return false;
    }

    public function deleteMultiple(iterable $keys): bool {
        return false;
    }

    public function get(string $key, mixed $default = null): mixed {
        return $default;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $default;
        }
        return $result;
    }

    public function has(string $key): bool {
        return false;
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool {
        return false;
    }

    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool {
        return false;
    }
}
