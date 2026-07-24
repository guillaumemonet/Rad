<?php

declare(strict_types=1);

namespace Rad\Test\Fixtures\Container;

/**
 * Depends on an interface: resolvable only when the interface is bound.
 */
final class Welcome {

    public function __construct(public readonly GreeterInterface $greeter) {

    }
}
