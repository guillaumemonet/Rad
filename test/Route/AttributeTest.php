<?php

declare(strict_types=1);

namespace Rad\Test\Route;

use PHPUnit\Framework\TestCase;
use Rad\Route\Attribute\AllowHeaders;
use Rad\Route\Attribute\Cacheable;
use Rad\Route\Attribute\Consume;
use Rad\Route\Attribute\Cors;
use Rad\Route\Attribute\ExposeHeaders;
use Rad\Route\Attribute\Get;
use Rad\Route\Attribute\Post;
use Rad\Route\Attribute\Produce;
use Rad\Route\Attribute\Session;
use Rad\Route\Attribute\Version;
use Rad\Route\Route;

/**
 * Unit tests for the route attributes: each attribute must mutate the Route
 * exactly like the legacy annotation it replaces.
 */
final class AttributeTest extends TestCase {

    public function testVerbSetsMethodAndPath(): void {
        $route = new Route();
        (new Get('/foo/'))->apply($route);

        $this->assertSame('GET', $route->getMethod());
        $this->assertSame('/foo/', $route->getPath());
    }

    public function testPostVerb(): void {
        $route = new Route();
        (new Post('/bar/'))->apply($route);

        $this->assertSame('POST', $route->getMethod());
    }

    public function testProduceAcceptsSeveralTypes(): void {
        $route = new Route();
        (new Produce('json', 'html'))->apply($route);

        $this->assertSame(['json', 'html'], $route->getProcucedMimeType());
    }

    public function testConsume(): void {
        $route = new Route();
        (new Consume('html'))->apply($route);

        $this->assertSame(['html'], $route->getConsumedMimeType());
    }

    public function testVersionIsCastToString(): void {
        $route = new Route();
        (new Version(3))->apply($route);

        $this->assertSame('3', $route->getVersion());
    }

    public function testSessionFlag(): void {
        $route = new Route();
        (new Session())->apply($route);

        $this->assertTrue($route->isSessionEnabled());
    }

    public function testCacheableFlag(): void {
        $route = new Route();
        (new Cacheable())->apply($route);

        $this->assertTrue($route->isCacheEnabled());
    }

    public function testCorsDomain(): void {
        $route = new Route();
        (new Cors('https://example.com'))->apply($route);

        $this->assertSame('https://example.com', $route->getCorsDomain());
    }

    public function testAllowHeaders(): void {
        $route = new Route();
        (new AllowHeaders('X-Foo', 'X-Bar'))->apply($route);

        $this->assertSame(['X-Foo', 'X-Bar'], $route->getAllowedHeaders());
    }

    /**
     * Guards the exposedHeaders property rename (was a dynamic property before).
     */
    public function testExposeHeaders(): void {
        $route = new Route();
        (new ExposeHeaders('X-Total-Count'))->apply($route);

        $this->assertSame(['X-Total-Count'], $route->getExposedHeaders());
    }
}
