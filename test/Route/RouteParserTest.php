<?php

declare(strict_types=1);

namespace Rad\Test\Route;

use PHPUnit\Framework\TestCase;
use Rad\Route\Route;
use Rad\Route\RouteParser;
use Rad\Test\Fixtures\AttributeController;
use Rad\Test\Fixtures\LegacyController;

/**
 * Integration tests for the attribute-based parser and its docblock fallback.
 */
final class RouteParserTest extends TestCase {
    public function testParsesAttributeRoutes(): void {
        $routes = RouteParser::parseRoutes([AttributeController::class]);

        $this->assertCount(2, $routes);

        $index = $this->findRoute($routes, 'GET', '/attr/');
        $this->assertNotNull($index);
        $this->assertSame(AttributeController::class, $index->getClassName());
        $this->assertSame('index', $index->getMethodName());
        $this->assertSame(['html'], $index->getProcucedMimeType());

        $create = $this->findRoute($routes, 'POST', '/attr/create/');
        $this->assertNotNull($create);
        $this->assertSame('create', $create->getMethodName());
        $this->assertSame('2', $create->getVersion());
        $this->assertSame(['json'], $create->getProcucedMimeType());
    }

    public function testFallsBackToLegacyDocblock(): void {
        $routes = RouteParser::parseRoutes([LegacyController::class]);

        $legacy = $this->findRoute($routes, 'GET', '/legacy/');
        $this->assertNotNull($legacy);
        $this->assertSame('index', $legacy->getMethodName());
        $this->assertSame(['html'], $legacy->getProcucedMimeType());
    }

    public function testNonControllerClassIsIgnored(): void {
        $routes = RouteParser::parseRoutes([\stdClass::class]);

        $this->assertSame([], $routes);
    }

    /**
     * @param Route[] $routes
     */
    private function findRoute(array $routes, string $method, string $path): ?Route {
        foreach ($routes as $route) {
            if ($route->getMethod() === $method && $route->getPath() === $path) {
                return $route;
            }
        }
        return null;
    }
}
