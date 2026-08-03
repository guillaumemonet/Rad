<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Event;

/**
 * A listener handling events dispatched through the PSR-14 dispatcher.
 *
 * @author Guillaume Monet
 */
interface EventListenerInterface {
    public function handle(object $event): void;
}
