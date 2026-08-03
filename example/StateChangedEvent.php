<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 */

use Rad\Event\AbstractEvent;

/**
 * Example PSR-14 event carrying a new state value.
 */
class StateChangedEvent extends AbstractEvent {
    public function __construct(public readonly int $state) {
    }
}
