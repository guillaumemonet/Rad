<?php

declare(strict_types=1);

namespace Rad\Test\Container;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Rad\Container\Container;
use Rad\Container\NotFoundException;
use Rad\Error\ContainerException;
use Rad\Test\Fixtures\Container\Alpha;
use Rad\Test\Fixtures\Container\FrenchGreeter;
use Rad\Test\Fixtures\Container\GreeterInterface;
use Rad\Test\Fixtures\Container\NeedsScalar;
use Rad\Test\Fixtures\Container\Welcome;
use Rad\Test\Fixtures\Container\WithDefault;

final class ContainerTest extends TestCase {
    private Container $container;

    protected function setUp(): void {
        $this->container = new Container();
    }

    public function testAutowiresConcreteClassWithoutRegistration(): void {
        $greeter = $this->container->get(FrenchGreeter::class);
        $this->assertInstanceOf(FrenchGreeter::class, $greeter);
    }

    public function testGetReturnsSameSingletonInstance(): void {
        $this->assertSame(
            $this->container->get(FrenchGreeter::class),
            $this->container->get(FrenchGreeter::class)
        );
    }

    public function testMakeReturnsFreshInstances(): void {
        $this->assertNotSame(
            $this->container->make(FrenchGreeter::class),
            $this->container->make(FrenchGreeter::class)
        );
    }

    public function testBindsInterfaceToImplementationAndAutowires(): void {
        $this->container->bind(GreeterInterface::class, FrenchGreeter::class);

        $welcome = $this->container->get(Welcome::class);
        $this->assertInstanceOf(FrenchGreeter::class, $welcome->greeter);
        $this->assertSame('Bonjour', $welcome->greeter->greet());
    }

    public function testUnboundInterfaceThrows(): void {
        $this->expectException(ContainerException::class);
        $this->container->get(Welcome::class);
    }

    public function testFactoryClosureBinding(): void {
        $this->container->singleton(GreeterInterface::class, fn () => new FrenchGreeter());
        $this->assertInstanceOf(FrenchGreeter::class, $this->container->get(GreeterInterface::class));
    }

    public function testInstanceRegistration(): void {
        $greeter = new FrenchGreeter();
        $this->container->instance(GreeterInterface::class, $greeter);
        $this->assertSame($greeter, $this->container->get(GreeterInterface::class));
    }

    public function testScalarWithDefaultIsHonored(): void {
        $this->container->bind(GreeterInterface::class, FrenchGreeter::class);
        $object = $this->container->get(WithDefault::class);
        $this->assertSame('!', $object->suffix);
    }

    public function testMakeOverridesParameterByName(): void {
        $object = $this->container->make(NeedsScalar::class, ['name' => 'Rad']);
        $this->assertSame('Rad', $object->name);
    }

    public function testRequiredScalarWithoutOverrideThrows(): void {
        $this->expectException(ContainerException::class);
        $this->container->make(NeedsScalar::class);
    }

    public function testUnknownClassThrowsNotFound(): void {
        $this->expectException(NotFoundException::class);
        $this->container->get('Rad\\Nope\\DoesNotExist');
    }

    public function testCircularDependencyThrows(): void {
        $this->expectException(ContainerException::class);
        $this->container->get(Alpha::class);
    }

    public function testHas(): void {
        $this->assertTrue($this->container->has(FrenchGreeter::class));
        $this->assertFalse($this->container->has('Rad\\Nope\\DoesNotExist'));

        $this->container->bind(GreeterInterface::class, FrenchGreeter::class);
        $this->assertTrue($this->container->has(GreeterInterface::class));
    }

    public function testCallAutowiresCallableParameters(): void {
        $this->container->bind(GreeterInterface::class, FrenchGreeter::class);
        $result = $this->container->call(fn (GreeterInterface $g) => $g->greet());
        $this->assertSame('Bonjour', $result);
    }

    public function testImplementsPsrContainer(): void {
        $this->assertInstanceOf(ContainerInterface::class, $this->container);
    }
}
