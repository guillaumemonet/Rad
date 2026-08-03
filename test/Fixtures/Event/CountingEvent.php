<?php

declare(strict_types=1);

namespace Rad\Test\Fixtures\Event;

use Rad\Event\AbstractEvent;

final class CountingEvent extends AbstractEvent {
    public int $count = 0;
}
