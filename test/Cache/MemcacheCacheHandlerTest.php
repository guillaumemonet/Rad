<?php

declare(strict_types=1);

namespace Rad\Test\Cache;

use Rad\Cache\CacheInterface;
use Rad\Cache\MemcacheCacheHandler;
use Rad\Config\Config;
use Throwable;

final class MemcacheCacheHandlerTest extends CacheContractTestCase {
    protected function createHandler(): CacheInterface {
        if (!extension_loaded('memcached')) {
            $this->markTestSkipped('ext-memcached is not installed');
        }
        Config::load();
        $config       = Config::getConfig()->services->cache->handlers->memcache->config;
        $config->host = getenv('MEMCACHED_HOST') ?: '127.0.0.1';
        $config->port = (int) (getenv('MEMCACHED_PORT') ?: 11211);
        try {
            $handler = new MemcacheCacheHandler();
            if (!$handler->set('__ping__', '1')) {
                $this->markTestSkipped('Memcached unavailable');
            }
            return $handler;
        } catch (Throwable $ex) {
            $this->markTestSkipped('Memcached unavailable: ' . $ex->getMessage());
        }
    }
}
