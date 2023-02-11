<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Controller;

use Rad\Observer\Observable;
use Rad\Route\Route;
use Rad\Worker\Orderer;

/*
 * Description of Controller
 *
 * @author Guillaume Monet
 */

abstract class Controller extends Observable {

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
     * Call For an asynchronous order
     * @param type $queue
     * @param type $messageType
     * @param type $message
     */
    protected function makeOrder($queue, $messageType, $message) {
        Orderer::sendMessage($queue, $messageType, $message);
    }

}
