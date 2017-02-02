<?php

namespace Rad\Cache;

use Rad\Config\Config;

/**
 * Description of CacheManager
 *
 * @author Guillaume Monet
 */
final class Memcache_Handler extends ICacheManager {

    private $memcache = null;

    public function __construct() {
        $this->memcache = new \Memcached();
        $this->memcache->addServer(Config::get('cache_memcache', 'url'), (int) Config::get('cache_memcache', 'port'), 100);
    }

    /**
     * Read values for a set of keys from cache
     * @param array $keys list of keys to fetch
     * @return array list of values with the given keys used as indexes
     * @return boolean true on success, false on failure
     */
    public function read(array $keys) {
        $res = $this->memcache->getMulti($keys);
        $_res = array();
        if (is_array($res) && sizeof($res) > 0) {
            foreach ($res as $k => $v) {
                $_res[$k] = $v;
            }
        }
        return $_res;
    }

    /**
     * Save values for a set of keys to cache
     * @param array $keys list of values to save
     * @param int $expire expiration time
     * @return boolean true on success, false on failure
     */
    public function write(array $keys, $expire = null) {
        if ($expire == null) {
            $expire = (int) Config::get("cache", "lifetime");
        }
        return $this->memcache->setMulti($keys, $expire);
    }

    /**
     * Remove values from cache
     * @param array $keys list of keys to delete
     * @return boolean true on success, false on failure
     */
    public function delete(array $keys) {
        foreach ($keys as $k) {
            $k = sha1($k);
            $this->memcache->delete($k);
        }
        return true;
    }

    /**
     * Remove *all* values from cache
     * @return boolean true on success, false on failure
     */
    public function purge() {
        return $this->memcache->flush();
    }

    /**
     * Display only stats usage
     */
    public function displayStats() {
        print_r($this->memcache->getExtendedStats());
    }

}
