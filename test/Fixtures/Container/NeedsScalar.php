<?php

declare(strict_types=1);

namespace Rad\Test\Fixtures\Container;

/**
 * A required scalar with no default cannot be autowired without an override.
 */
final class NeedsScalar {

    public function __construct(public readonly string $name) {

    }
}
