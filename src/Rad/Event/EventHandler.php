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

/**
 * Default File Logger
 *
 * @author guillaume
 */
class EventHandler implements EventDispatcherInterface, ListenerProviderInterface {

    private $listeners = [];

    public function addListener(string $eventName, EventListenerInterface $listener) {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }
        $this->listeners[$eventName][] = $listener;
    }

    public function dispatch(object $event) {
        $eventName = get_class($event);
        $listeners = $this->listeners[$eventName] ?? [];

        array_walk($listeners, static fn(EventListenerInterface $listener) => $listener->handle($event));
    }

    public function getListenersForEvent(AbstractEvent $event): iterable {
        $eventName = get_class($event);
        return $this->listeners[$eventName] ?? [];
    }
}
