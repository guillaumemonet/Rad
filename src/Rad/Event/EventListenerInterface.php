<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Event;

/**
 * Description of EventListenerInterface
 *
 * @author Guillaume Monet
 */
interface EventListenerInterface {

    public function handle(AbstractEvent $event);
}
