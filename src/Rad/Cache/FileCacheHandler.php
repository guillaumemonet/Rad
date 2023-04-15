<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Cache;

use Psr\SimpleCache\CacheInterface;
use Rad\Config\Config;
use Rad\Encryption\Encryption;

/**
 * FileCacheManager
 * Use files to cache datas
 *
 * @author Guillaume Monet
 */
class FileCacheHandler implements CacheInterface {

    private $path       = null;
    private $defaultTTL = null;

    public function __construct() {
        $config           = Config::getServiceConfig('cache', 'file')->config;
        $this->path       = Config::getApiConfig()->install_path . $config->path;
        $this->defaultTTL = $config->lifetime;
    }

    /**
     * 
     * @param type $key
     * @return bool
     */
    public function delete($key): bool {
        $md5 = Encryption::hashMd5($key);
        return unlink($this->path . $md5);
    }

    /**
     * 
     * @param type $keys
     * @return bool
     */
    public function deleteMultiple($keys): bool {
        $ret = false;
        foreach ($keys as $k) {
            $ret &= $this->delete($k);
        }
        return $ret;
    }

    /**
     * 
     * @param type $key
     * @param type $default
     * @return type
     */
    public function get($key, $default = null) {
        $md5 = Encryption::hashMd5($key);
        if (file_exists($this->path . $md5) && ($tmp = file_get_contents($this->path . $md5)) !== false) {
            return $tmp;
        } else {
            return $default;
        }
    }

    /**
     * 
     * @param type $keys
     * @param type $default
     * @return array
     */
    public function getMultiple($keys, $default = null): array {
        $ret = [];
        foreach ($keys as $k) {
            $ret[$k] = $this->get($k, $default);
        }
        return $ret;
    }

    /**
     * 
     * @param type $key
     * @return bool
     */
    public function has($key): bool {
        return file_exists($this->path . Encryption::hashMd5($key));
    }

    /**
     * 
     * @param type $key
     * @param type $value
     * @param type $ttl
     * @return bool
     */
    public function set($key, $value, $ttl = null): bool {
        return file_put_contents($this->path . Encryption::hashMd5($key), $value, LOCK_EX);
    }

    /**
     * 
     * @param type $values
     * @param type $ttl
     * @return bool
     */
    public function setMultiple($values, $ttl = null): bool {
        $ret = false;
        foreach ($values as $k => $v) {
            $ret &= $this->set($k, $v, $ttl);
        }
        return $ret;
    }

    /**
     * 
     * @return bool
     */
    public function clear(): bool {
        $t      = time();
        $handle = opendir($this->path);
        while ($handle !== false && false !== ($file   = readdir($handle))) {
            if ($file != ".." && $file != "." && @filectime($this->path . $file) < ($t - (int) $this->defaultTTL)) {
                @unlink($this->path . $file);
            }
        }
        return true;
    }

    /**
     * 
     * @return bool
     */
    public function purge(): bool {
        $handle = opendir($this->path);
        while ($handle !== false && false !== ($file   = readdir($handle))) {
            if ($file != ".." && $file != ".") {
                @unlink($this->path . $file);
            }
        }
    }

}
