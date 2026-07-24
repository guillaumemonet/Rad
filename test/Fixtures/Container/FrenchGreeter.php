<?php

declare(strict_types=1);

namespace Rad\Test\Fixtures\Container;

final class FrenchGreeter implements GreeterInterface {

    public function greet(): string {
        return 'Bonjour';
    }
}
