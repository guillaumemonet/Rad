<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Container;

use Closure;
use Psr\Container\ContainerInterface;
use Rad\Error\ContainerException;
use ReflectionClass;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Lightweight PSR-11 container with constructor autowiring.
 *
 * - bind()/singleton(): register how an identifier resolves (class name,
 *   factory closure, or itself for plain autowiring).
 * - instance(): register an already built object.
 * - get(): resolve with singleton semantics (built once, then cached).
 * - make(): resolve a fresh instance, optionally overriding constructor
 *   arguments by name (used e.g. to inject the current Route into a controller).
 * - call(): invoke any callable, autowiring its parameters.
 *
 * Dependencies are resolved from constructor type-hints via reflection;
 * scalar/optional parameters fall back to their default value or null.
 */
final class Container implements ContainerInterface {

    /** @var array<string, array{concrete: Closure|string, shared: bool}> */
    private array $bindings = [];

    /** @var array<string, object> */
    private array $instances = [];

    /** @var array<string, true> guards against circular dependencies */
    private array $building = [];

    /**
     * Register a binding. $concrete may be a class-string, a factory closure
     * (fn(Container $c, array $params) => object) or null to autowire $id itself.
     */
    public function bind(string $id, Closure|string|null $concrete = null, bool $shared = false): void {
        unset($this->instances[$id]);
        $this->bindings[$id] = ['concrete' => $concrete ?? $id, 'shared' => $shared];
    }

    /**
     * Register a shared binding (resolved once, then reused).
     */
    public function singleton(string $id, Closure|string|null $concrete = null): void {
        $this->bind($id, $concrete, true);
    }

    /**
     * Register an already-constructed object under $id.
     */
    public function instance(string $id, object $instance): object {
        $this->instances[$id] = $instance;
        return $instance;
    }

    public function has(string $id): bool {
        return isset($this->bindings[$id]) || isset($this->instances[$id]) || class_exists($id) || interface_exists($id);
    }

    public function get(string $id): mixed {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }
        $object = $this->resolve($id);
        // Cache singletons and plain autowired classes so repeated get() keeps identity.
        if ($this->bindings[$id]['shared'] ?? true) {
            $this->instances[$id] = $object;
        }
        return $object;
    }

    /**
     * Resolve a fresh instance, overriding constructor arguments by name.
     *
     * @param array<string, mixed> $parameters
     */
    public function make(string $id, array $parameters = []): mixed {
        return $this->resolve($id, $parameters);
    }

    /**
     * Invoke a callable, autowiring its parameters (overridable by name).
     *
     * @param array<string, mixed> $parameters
     */
    public function call(callable $callable, array $parameters = []): mixed {
        $reflection = new ReflectionFunction(Closure::fromCallable($callable));
        $args       = array_map(
                fn(ReflectionParameter $p) => $this->resolveParameter($p, $parameters),
                $reflection->getParameters()
        );
        return $callable(...$args);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function resolve(string $id, array $parameters = []): mixed {
        if (isset($this->bindings[$id])) {
            $concrete = $this->bindings[$id]['concrete'];
            if ($concrete instanceof Closure) {
                return $concrete($this, $parameters);
            }
            if ($concrete !== $id) {
                return $this->build($concrete, $parameters);
            }
        }
        return $this->build($id, $parameters);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function build(string $class, array $parameters): object {
        if (isset($this->building[$class])) {
            throw new ContainerException('Circular dependency detected while building ' . $class);
        }
        if (!class_exists($class)) {
            throw new NotFoundException('No binding or class found for "' . $class . '"');
        }
        $reflection = new ReflectionClass($class);
        if (!$reflection->isInstantiable()) {
            throw new ContainerException('Cannot instantiate ' . $class . ': abstract or interface without a binding');
        }
        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return new $class();
        }
        $this->building[$class] = true;
        try {
            $args = array_map(
                    fn(ReflectionParameter $p) => $this->resolveParameter($p, $parameters),
                    $constructor->getParameters()
            );
        } finally {
            unset($this->building[$class]);
        }
        return $reflection->newInstanceArgs($args);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function resolveParameter(ReflectionParameter $param, array $parameters): mixed {
        $name = $param->getName();
        if (array_key_exists($name, $parameters)) {
            return $parameters[$name];
        }
        $type = $param->getType();
        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            try {
                return $this->get($type->getName());
            } catch (ContainerException $ex) {
                return $this->fallback($param, $ex);
            }
        }
        return $this->fallback($param, null);
    }

    private function fallback(ReflectionParameter $param, ?ContainerException $ex): mixed {
        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }
        if ($param->allowsNull()) {
            return null;
        }
        if ($ex !== null) {
            throw $ex;
        }
        $class = $param->getDeclaringClass();
        throw new ContainerException(sprintf(
                        'Cannot autowire parameter $%s of %s',
                        $param->getName(),
                        $class !== null ? $class->getName() : '{closure}'
        ));
    }
}
