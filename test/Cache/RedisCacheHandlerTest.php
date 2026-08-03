<?php

declare(strict_types=1);

namespace Rad\Test\Cache;

use Rad\Cache\CacheInterface;
use Rad\Cache\RedisCacheHandler;
use Rad\Config\Config;
use Throwable;

final class RedisCacheHandlerTest extends CacheContractTestCase {
    protected function createHandler(): CacheInterface {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('ext-redis is not installed');
        }
        Config::load();
        $config       = Config::getConfig()->services->cache->handlers->redis->config;
        $config->host = getenv('REDIS_HOST') ?: '127.0.0.1';
        $config->port = (int) (getenv('REDIS_PORT') ?: 6379);
        try {
            return new RedisCacheHandler();
        } catch (Throwable $ex) {
            $this->markTestSkipped('Redis unavailable: ' . $ex->getMessage());
        }
    }
}
