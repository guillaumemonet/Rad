<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Cache;

use Memcached;
use Psr\SimpleCache\CacheInterface;
use Rad\Config\Config;
use Rad\Encryption\Encryption;

/**
 * Description of CacheManager
 *
 * @author Guillaume Monet
 */
final class MemcacheCacheHandler implements CacheInterface {

    /**
     * 
     * @var Memcached
     */
    private $memcache   = null;
    private $defaultTTL = null;

    public function __construct() {
        $config           = Config::getServiceConfig('cache', 'memcache')->config;
        $this->memcache   = new Memcached();
        $this->memcache->addServer($config->host, (int) $config->port, 100);
        $this->defaultTTL = isset($config->lifetime) ? $config->lifetime : 3600;
    }

    /**
     * 
     * @return bool
     */
    public function clear(): bool {
        return $this->memcache->flush();
    }

    /**
     * 
     * @param type $key
     */
    public function delete($key) {
        $this->memcache->delete(Encryption::hashMd5($key));
    }

    /**
     * 
     * @param type $keys
     * @return bool
     */
    public function deleteMultiple($keys): bool {
        $nkeys = array_map(function ($v) {
            return Encryption::hashMd5($v);
        }, $keys);
        $this->memcache->deleteMulti($nkeys);
        return true;
    }

    /**
     * 
     * @param type $key
     * @param type $default
     * @return type
     */
    public function get($key, $default = null) {
        return $this->memcache->get(Encryption::hashMd5($key));
    }

    /**
     * 
     * @param type $keys
     * @param type $default
     * @return type
     */
    public function getMultiple($keys, $default = null) {
        $nkeys = array_map(function ($v) {
            return Encryption::hashMd5($v);
        }, $keys);
        $values = $this->memcache->getMulti($nkeys, Memcached::GET_PRESERVE_ORDER);
        return array_combine($keys, array_values($values));
    }

    /**
     * 
     * @param type $key
     * @return bool
     */
    public function has($key): bool {
        return !empty($this->get(Encryption::hashMd5($key)));
    }

    /**
     * 
     * @param type $key
     * @param type $value
     * @param type $ttl
     * @return bool
     */
    public function set($key, $value, $ttl = null): bool {
        if ($ttl == null) {
            $ttl = (int) $this->defaultTTL;
        }
        return $this->memcache->set(Encryption::hashMd5($key), $value, $ttl);
    }

    /**
     * 
     * @param type $values
     * @param type $ttl
     * @return bool
     */
    public function setMultiple($values, $ttl = null): bool {
        if ($ttl == null) {
            $ttl = (int) $this->defaultTTL;
        }
        $keys = array_map(function ($v) {
            return Encryption::hashMd5($v);
        }, array_keys($values));
        $nvalues = array_combine($keys, $values);
        return $this->memcache->setMulti($nvalues, $ttl);
    }

}
