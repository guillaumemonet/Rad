<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Event;

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Base class for events dispatched through the PSR-14 event dispatcher.
 */
abstract class AbstractEvent implements StoppableEventInterface {
    private bool $propagationStopped = false;

    public function isPropagationStopped(): bool {
        return $this->propagationStopped;
    }

    public function stopPropagation(): void {
        $this->propagationStopped = true;
    }
}
