<?php

declare(strict_types=1);

namespace Rad\Test\Event;

use PHPUnit\Framework\TestCase;
use Rad\Event\AbstractEvent;
use Rad\Event\EventHandler;
use Rad\Event\EventListenerInterface;
use Rad\Test\Fixtures\Event\CountingEvent;

final class EventHandlerTest extends TestCase {
    public function testDispatchInvokesListenersAndReturnsEvent(): void {
        $handler = new EventHandler();
        $handler->addListener(CountingEvent::class, $this->incrementer());

        $event  = new CountingEvent();
        $result = $handler->dispatch($event);

        $this->assertSame($event, $result);
        $this->assertSame(1, $event->count);
    }

    public function testListenersAreScopedByEventClass(): void {
        $handler = new EventHandler();
        $handler->addListener('Some\\Other\\Event', $this->incrementer());

        $event = new CountingEvent();
        $handler->dispatch($event);

        $this->assertSame(0, $event->count);
    }

    public function testStopPropagationHaltsRemainingListeners(): void {
        $handler = new EventHandler();
        $handler->addListener(CountingEvent::class, new class () implements EventListenerInterface {
            public function handle(object $event): void {
                $event->count++;
                if ($event instanceof AbstractEvent) {
                    $event->stopPropagation();
                }
            }
        });
        $handler->addListener(CountingEvent::class, $this->incrementer());

        $event = new CountingEvent();
        $handler->dispatch($event);

        $this->assertSame(1, $event->count);
    }

    private function incrementer(): EventListenerInterface {
        return new class () implements EventListenerInterface {
            public function handle(object $event): void {
                $event->count++;
            }
        };
    }
}
