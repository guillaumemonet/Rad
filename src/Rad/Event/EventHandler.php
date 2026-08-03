<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Event;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Minimal PSR-14 event dispatcher and listener provider.
 *
 * @author guillaume
 */
class EventHandler implements EventDispatcherInterface, ListenerProviderInterface {
    /** @var array<string, EventListenerInterface[]> */
    private array $listeners = [];

    public function addListener(string $eventName, EventListenerInterface $listener): void {
        $this->listeners[$eventName][] = $listener;
    }

    public function dispatch(object $event): object {
        foreach ($this->getListenersForEvent($event) as $listener) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }
            $listener->handle($event);
        }
        return $event;
    }

    /**
     * @return iterable<EventListenerInterface>
     */
    public function getListenersForEvent(object $event): iterable {
        return $this->listeners[get_class($event)] ?? [];
    }
}
