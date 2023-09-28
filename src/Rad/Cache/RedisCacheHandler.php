<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Cache;

use Rad\Config\Config;
use Rad\Encryption\Encryption;
use Redis;

/**
 * Description of Redis_Handler
 *
 * @author Guillaume Monet
 */
final class RedisCacheHandler implements CacheInterface {

    /**
     * @var Redis
     */
    private $redis = null;

    public function __construct() {
        $config      = Config::getServiceConfig('cache', 'redis')->config;
        $this->redis = new Redis();
        $this->connect($config->host, $config->port);
    }

    public function clear(): bool {
        $this->redis->flushDB();
        return true;
    }

    public function purge(): bool {
        return true;
    }

    public function delete($key) {
        return $this->redis->del(Encryption::hashMd5($key));
    }

    public function deleteMultiple($keys): bool {
        $ret = true;
        foreach ($keys as $key) {
            $ret &= $this->delete(Encryption::hashMd5($key));
        }
        return $ret;
    }

    public function get($key, $default = null) {
        return $this->redis->get(Encryption::hashMd5($key));
    }

    public function getMultiple($keys, $default = null) {
        $ret = [];
        foreach ($keys as $k) {
            $ret[$k] = $this->get($k, $default);
        }
        return $ret;
    }

    public function has($key): bool {
        return $this->exists(Encryption::hashMd5($key));
    }

    public function set($key, $value, $ttl = null): bool {
        if ($ttl === null) {
            return $this->set(Encryption::hashMd5($key), $value);
        } else {
            return $this->setex(Encryption::hashMd5($key), $ttl, $value);
        }
    }

    public function setMultiple($values, $ttl = null): bool {
        $ret = true;
        foreach ($values as $key => $value) {
            $ret &= $this->set($key, $value, $ttl);
        }
        return $ret;
    }
}
