<?php

declare(strict_types=1);

namespace Rad\Test\Cache;

use Rad\Cache\CacheInterface;
use Rad\Cache\QuickCacheHandler;

final class QuickCacheHandlerTest extends CacheContractTestCase {
    protected function createHandler(): CacheInterface {
        return new QuickCacheHandler();
    }
}
