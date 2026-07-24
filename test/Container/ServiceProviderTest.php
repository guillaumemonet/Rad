<?php

declare(strict_types=1);

namespace Rad\Test\Container;

use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Rad\Cache\Cache;
use Rad\Cache\CacheInterface;
use Rad\Container\Container;
use Rad\Container\ServiceProvider;
use Rad\Encryption\Encryption;
use Rad\Encryption\EncryptionInterface;

/**
 * Verifies the legacy service facades are reachable through the container and
 * resolve to the very same handler the facade exposes.
 */
final class ServiceProviderTest extends TestCase {

    private Container $container;

    protected function setUp(): void {
        $this->container = new Container();
        ServiceProvider::register($this->container);
    }

    public function testCacheInterfaceResolvesToConfiguredDefault(): void {
        $cache = $this->container->get(CacheInterface::class);
        $this->assertInstanceOf(CacheInterface::class, $cache);
        $this->assertSame(Cache::getHandler(), $cache);
    }

    public function testLoggerInterfaceResolvesToAbstractLogger(): void {
        $logger = $this->container->get(LoggerInterface::class);
        $this->assertInstanceOf(AbstractLogger::class, $logger);
    }

    public function testEncryptionInterfaceResolvesToConfiguredDefault(): void {
        $encryption = $this->container->get(EncryptionInterface::class);
        $this->assertInstanceOf(EncryptionInterface::class, $encryption);
        $this->assertSame(Encryption::getHandler(), $encryption);
    }

    public function testBindingDelegatesOnEachResolution(): void {
        // Non-shared bridge: the container must not cache its own copy.
        $this->assertSame(
                $this->container->get(CacheInterface::class),
                $this->container->get(CacheInterface::class)
        );
        // ...and it is the facade's instance, not a container-built one.
        $this->assertSame(Cache::getHandler(), $this->container->get(CacheInterface::class));
    }
}
