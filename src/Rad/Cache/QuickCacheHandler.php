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
 * Volatile Cache
 * To Share Var Between Function
 */
class QuickCacheHandler extends AbstractCacheHandler {
    /**
     * @var array<string, mixed>
     */
    private array $datas = [];

    public function clear(): bool {
        $this->datas = [];
        return true;
    }

    public function purge(): bool {
        return true;
    }

    public function delete(string $key): bool {
        unset($this->datas['key_' . $key]);
        return true;
    }

    public function deleteMultiple(iterable $keys): bool {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return true;
    }

    public function get(string $key, mixed $default = null): mixed {
        return $this->datas['key_' . $key] ?? $default;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable {
        $ret = [];
        foreach ($keys as $key) {
            $ret[$key] = $this->get($key, $default);
        }
        return $ret;
    }

    public function has(string $key): bool {
        return isset($this->datas['key_' . $key]);
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool {
        $this->datas['key_' . $key] = $value;
        return true;
    }

    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        return true;
    }
}
