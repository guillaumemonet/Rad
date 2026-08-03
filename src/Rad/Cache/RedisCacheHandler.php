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
use Rad\Config\Config;
use Rad\Encryption\Encryption;
use Redis;

/**
 * Description of Redis_Handler
 *
 * @author Guillaume Monet
 */
final class RedisCacheHandler extends AbstractCacheHandler {
    private Redis $redis;

    public function __construct() {
        $config      = Config::getServiceConfig('cache', 'redis')->config;
        $this->redis = new Redis();
        $this->redis->connect($config->host, (int) $config->port);
    }

    public function clear(): bool {
        $this->redis->flushDB();
        return true;
    }

    public function purge(): bool {
        return true;
    }

    public function delete(string $key): bool {
        return $this->redis->del(Encryption::hashMd5($key)) > 0;
    }

    public function deleteMultiple(iterable $keys): bool {
        $ret = true;
        foreach ($keys as $key) {
            $ret = $this->delete($key) && $ret;
        }
        return $ret;
    }

    public function get(string $key, mixed $default = null): mixed {
        $value = $this->redis->get(Encryption::hashMd5($key));
        return $value === false ? $default : $value;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable {
        $ret = [];
        foreach ($keys as $k) {
            $ret[$k] = $this->get($k, $default);
        }
        return $ret;
    }

    public function has(string $key): bool {
        return $this->redis->exists(Encryption::hashMd5($key)) > 0;
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool {
        $hash    = Encryption::hashMd5($key);
        $seconds = $this->ttlToSeconds($ttl);
        return $seconds === null ? $this->redis->set($hash, $value) : $this->redis->setex($hash, $seconds, $value);
    }

    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool {
        $ret = true;
        foreach ($values as $key => $value) {
            $ret = $this->set($key, $value, $ttl) && $ret;
        }
        return $ret;
    }
}
