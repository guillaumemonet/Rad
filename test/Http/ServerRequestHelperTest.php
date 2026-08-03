<?php

declare(strict_types=1);

namespace Rad\Test\Http;

use PHPUnit\Framework\TestCase;
use Rad\Http\HttpFactory;
use Rad\Http\ServerRequestHelper;

final class ServerRequestHelperTest extends TestCase {
    public function testIsXhr(): void {
        $factory = new HttpFactory();
        $plain   = $factory->createServerRequest('GET', '/');
        $xhr     = $plain->withHeader('X-Requested-With', 'XMLHttpRequest');

        $this->assertFalse(ServerRequestHelper::isXhr($plain));
        $this->assertTrue(ServerRequestHelper::isXhr($xhr));
    }

    public function testAllowsCache(): void {
        $factory = new HttpFactory();
        $request = $factory->createServerRequest('GET', '/');

        $this->assertTrue(ServerRequestHelper::allowsCache($request));
        $this->assertFalse(ServerRequestHelper::allowsCache($request->withHeader('Cache-Control', 'no-cache')));
    }

    public function testIsSecureFromScheme(): void {
        $factory = new HttpFactory();
        $this->assertTrue(ServerRequestHelper::isSecure($factory->createServerRequest('GET', 'https://example.com/')));
        $this->assertFalse(ServerRequestHelper::isSecure($factory->createServerRequest('GET', 'http://example.com/')));
    }
}
