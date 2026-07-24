<?php

declare(strict_types=1);

namespace Rad\Test\Fixtures\Container;

final class Alpha {

    public function __construct(public readonly Beta $beta) {

    }
}
