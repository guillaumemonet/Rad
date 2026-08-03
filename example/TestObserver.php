<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 */

use Rad\Event\EventListenerInterface;

/**
 * Example PSR-14 listener reacting to StateChangedEvent.
 *
 * @author Guillaume Monet
 */
class TestObserver implements EventListenerInterface {
    public function handle(object $event): void {
        if ($event instanceof StateChangedEvent) {
            error_log("State Change to " . $event->state);
        }
    }
}
