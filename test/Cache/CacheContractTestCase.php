<?php

declare(strict_types=1);

namespace Rad\Test\Cache;

use PHPUnit\Framework\TestCase;
use Rad\Cache\CacheInterface;

/**
 * Shared PSR-16 behaviour every writable cache handler must satisfy.
 */
abstract class CacheContractTestCase extends TestCase {
    protected CacheInterface $cache;

    abstract protected function createHandler(): CacheInterface;

    protected function setUp(): void {
        $this->cache = $this->createHandler();
        $this->cache->clear();
    }

    public function testSetGetHasDelete(): void {
        $this->assertFalse($this->cache->has('k1'));
        $this->assertSame('fallback', $this->cache->get('k1', 'fallback'));

        $this->assertTrue($this->cache->set('k1', 'value1'));
        $this->assertTrue($this->cache->has('k1'));
        $this->assertSame('value1', $this->cache->get('k1'));

        $this->assertTrue($this->cache->delete('k1'));
        $this->assertFalse($this->cache->has('k1'));
    }

    public function testMultiple(): void {
        $this->assertTrue($this->cache->setMultiple(['a' => '1', 'b' => '2']));

        $values = $this->cache->getMultiple(['a', 'b', 'missing'], 'def');
        $values = is_array($values) ? $values : iterator_to_array($values);

        $this->assertSame('1', $values['a']);
        $this->assertSame('2', $values['b']);
        $this->assertSame('def', $values['missing']);
    }

    public function testClear(): void {
        $this->cache->set('x', 'y');
        $this->assertTrue($this->cache->clear());
        $this->assertFalse($this->cache->has('x'));
    }
}
