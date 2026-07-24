<?php

declare(strict_types=1);

namespace Rad\Test\Fixtures\Container;

final class Beta {

    public function __construct(public readonly Alpha $alpha) {

    }
}
