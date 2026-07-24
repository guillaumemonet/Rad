<?php

declare(strict_types=1);

namespace Rad\Test\Fixtures\Container;

/**
 * Mixes an autowired dependency with a scalar that has a default value.
 */
final class WithDefault {

    public function __construct(
            public readonly GreeterInterface $greeter,
            public readonly string $suffix = '!'
    ) {

    }
}
