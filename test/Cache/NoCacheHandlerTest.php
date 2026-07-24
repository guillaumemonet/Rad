<?php

declare(strict_types=1);

namespace Rad\Test\Cache;

use PHPUnit\Framework\TestCase;
use Rad\Cache\NoCacheHandler;

final class NoCacheHandlerTest extends TestCase {

    public function testNeverStores(): void {
        $cache = new NoCacheHandler();
        $this->assertFalse($cache->set('k', 'v'));
        $this->assertFalse($cache->has('k'));
        $this->assertSame('default', $cache->get('k', 'default'));
        $this->assertTrue($cache->clear());
    }

    public function testGetMultipleReturnsDefaults(): void {
        $cache  = new NoCacheHandler();
        $values = $cache->getMultiple(['a', 'b'], 'x');
        $values = is_array($values) ? $values : iterator_to_array($values);
        $this->assertSame(['a' => 'x', 'b' => 'x'], $values);
    }
}
