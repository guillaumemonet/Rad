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
use DirectoryIterator;
use Rad\Config\Config;
use Rad\Encryption\Encryption;

/**
 * FileCacheManager
 * Use files to cache datas
 *
 * @author Guillaume Monet
 */
class FileCacheHandler extends AbstractCacheHandler {

    private string $path;
    private int $defaultTTL;

    public function __construct() {
        $config           = Config::getServiceConfig('cache', 'file')->config;
        $this->path       = Config::getApiConfig()->install_path . $config->path;
        $this->defaultTTL = (int) $config->lifetime;
    }

    public function delete(string $key): bool {
        $file = $this->path . Encryption::hashMd5($key);
        return !file_exists($file) || unlink($file);
    }

    public function deleteMultiple(iterable $keys): bool {
        $ret = true;
        foreach ($keys as $k) {
            $ret = $this->delete($k) && $ret;
        }
        return $ret;
    }

    public function get(string $key, mixed $default = null): mixed {
        $filePath = $this->path . Encryption::hashMd5($key);
        if (file_exists($filePath) && ($tmp = file_get_contents($filePath)) !== false) {
            return $tmp;
        }
        return $default;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable {
        $ret = [];
        foreach ($keys as $k) {
            $ret[$k] = $this->get($k, $default);
        }
        return $ret;
    }

    public function has(string $key): bool {
        return file_exists($this->path . Encryption::hashMd5($key));
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool {
        return file_put_contents($this->path . Encryption::hashMd5($key), $value, LOCK_EX) !== false;
    }

    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool {
        $ret = true;
        foreach ($values as $k => $v) {
            $ret = $this->set($k, $v, $ttl) && $ret;
        }
        return $ret;
    }

    public function purge(): bool {
        $t        = time();
        $iterator = new DirectoryIterator($this->path);
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile() && !$fileInfo->isDot() && $fileInfo->getCTime() < ($t - $this->defaultTTL)) {
                unlink($fileInfo->getPathname());
            }
        }
        return true;
    }

    public function clear(): bool {
        $iterator = new DirectoryIterator($this->path);
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile() && !$fileInfo->isDot()) {
                unlink($fileInfo->getPathname());
            }
        }
        return true;
    }
}
