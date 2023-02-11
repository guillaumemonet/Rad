<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Template;

use Rad\Cache\Cache;
use Smarty_CacheResource_KeyValueStore;

/**
 * 
 */
class SmartyTemplateCacheHandler extends Smarty_CacheResource_KeyValueStore {

    private $cacheType = null;

    public function __construct($cacheType) {
        $this->cacheType = $cacheType;
    }

    /**
     * 
     * @param array $keys
     * @return array
     */
    public function read(array $keys): array {
        return Cache::getHandler($this->cacheType)->getMultiple($keys);
    }

    public function write(array $keys, $expire = null) {
        Cache::getHandler($this->cacheType)->setMultiple($keys, $expire);
    }

    public function delete(array $keys) {
        Cache::getHandler($this->cacheType)->deleteMultiple($keys);
    }

    public function purge() {
        Cache::getHandler($this->cacheType)->clear();
    }

}
