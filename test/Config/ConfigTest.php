<?php

declare(strict_types=1);

namespace Rad\Test\Config;

use PHPUnit\Framework\TestCase;
use Rad\Config\Config;

/**
 * Ensures the runtime config is a consistent object graph regardless of the
 * accessor used (regression test for the array/object incoherence).
 */
final class ConfigTest extends TestCase {

    protected function setUp(): void {
        Config::load();
    }

    public function testConfigIsAnObjectAfterDefaultLoad(): void {
        $this->assertIsObject(Config::getConfig());
    }

    public function testGetApiConfig(): void {
        $this->assertSame('Rad\\Route\\Router', Config::getApiConfig('router'));
    }

    public function testGetApiConfigUnknownKeyReturnsNull(): void {
        $this->assertNull(Config::getApiConfig('does_not_exist'));
    }

    public function testGetSectionAndRow(): void {
        $this->assertIsObject(Config::get('api'));
        $this->assertSame('http://localhost:8000/', Config::get('api', 'url'));
    }

    public function testGetUnknownReturnsNull(): void {
        $this->assertNull(Config::get('nope'));
        $this->assertNull(Config::get('api', 'nope'));
    }

    public function testServiceConfigIsReadableAsObject(): void {
        $pdo = Config::getServiceConfig('database', 'pdo');
        $this->assertSame('mysql', $pdo->config->type);
    }

    public function testHasAndSet(): void {
        $this->assertTrue(Config::has('api'));
        $this->assertTrue(Config::has('api', 'url'));
        $this->assertFalse(Config::has('api', 'missing'));

        Config::set('custom', 'flag', true);
        $this->assertTrue(Config::has('custom', 'flag'));
        $this->assertTrue(Config::get('custom', 'flag'));
    }
}
