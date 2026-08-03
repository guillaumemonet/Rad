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
use Memcached;
use Rad\Config\Config;
use Rad\Encryption\Encryption;

/**
 * Description of CacheManager
 *
 * @author Guillaume Monet
 */
final class MemcacheCacheHandler extends AbstractCacheHandler {
    private Memcached $memcache;
    private int $defaultTTL;

    public function __construct() {
        $config         = Config::getServiceConfig('cache', 'memcache')->config;
        $this->memcache = new Memcached();
        $this->memcache->addServer($config->host, (int) $config->port, 100);
        $this->defaultTTL = isset($config->lifetime) ? (int) $config->lifetime : 3600;
    }

    public function clear(): bool {
        return $this->memcache->flush();
    }

    public function purge(): bool {
        return true;
    }

    public function delete(string $key): bool {
        return $this->memcache->delete(Encryption::hashMd5($key));
    }

    public function deleteMultiple(iterable $keys): bool {
        $nkeys = array_map(static fn ($v) => Encryption::hashMd5($v), $this->toArray($keys));
        $this->memcache->deleteMulti($nkeys);
        return true;
    }

    public function get(string $key, mixed $default = null): mixed {
        $value = $this->memcache->get(Encryption::hashMd5($key));
        return $this->memcache->getResultCode() === Memcached::RES_NOTFOUND ? $default : $value;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable {
        $ret = [];
        foreach ($keys as $k) {
            $ret[$k] = $this->get($k, $default);
        }
        return $ret;
    }

    public function has(string $key): bool {
        $this->memcache->get(Encryption::hashMd5($key));
        return $this->memcache->getResultCode() !== Memcached::RES_NOTFOUND;
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool {
        $seconds = $this->ttlToSeconds($ttl) ?? $this->defaultTTL;
        return $this->memcache->set(Encryption::hashMd5($key), $value, $seconds);
    }

    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool {
        $seconds = $this->ttlToSeconds($ttl) ?? $this->defaultTTL;
        $values  = $this->toArray($values);
        $keys    = array_map(static fn ($v) => Encryption::hashMd5($v), array_keys($values));
        $nvalues = array_combine($keys, array_values($values));
        return $this->memcache->setMulti($nvalues, $seconds);
    }

    /**
     * @param iterable<mixed> $items
     * @return array<mixed>
     */
    private function toArray(iterable $items): array {
        return is_array($items) ? $items : iterator_to_array($items);
    }
}
