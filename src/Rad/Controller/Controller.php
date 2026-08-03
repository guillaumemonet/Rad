<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Controller;

use Rad\Event\Event;
use Rad\Route\Route;
use Rad\Worker\Orderer;

/*
 * Description of Controller
 *
 * @author Guillaume Monet
 */

abstract class Controller {
    /**
     *
     * @var Route
     */
    protected $route;

    /**
     *
     * @param Route $route
     */
    public function __construct(Route $route = null) {
        $this->route = $route;
    }

    /**
     *
     * @return Route
     */
    public function getRoute(): Route {
        return $this->route;
    }

    /**
     * Dispatch a PSR-14 event to the registered listeners.
     */
    protected function dispatch(object $event): object {
        return Event::getHandler()->dispatch($event);
    }

    /**
     * Call For an asynchronous order
     */
    protected function makeOrder($queue, $messageType, $message) {
        Orderer::sendMessage($queue, $messageType, $message);
    }

}
