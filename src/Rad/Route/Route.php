<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Rad\Route;

use Rad\Observer\Observable;

/**
 * Description of Route
 *
 * @author guillaume
 */
class Route {

    use RouteSetterTrait;
    use RouteGetterTrait;

    protected $version = 1;
    protected $className;
    protected $methodName;
    protected $verb;
    protected $regex;
    protected $middlewares = array();
    protected $produce = null;
    protected $consume = null;
    protected $observers = array();
    protected $args = array();

    public function applyObservers(Observable $observable) {
        array_map(function($observer) use ($observable) {
            $obs = new $observer();
            $observable->attach($obs);
        }, $this->observers);
    }

    public function __toString() {
        return "Route " . strtoupper($this->verb) . " : /v" . $this->version . "/" . $this->regex . " call " . $this->className . "->" . $this->methodName;
    }

}
