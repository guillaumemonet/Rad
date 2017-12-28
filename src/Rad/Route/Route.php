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
    protected $className = null;
    protected $methodName = null;
    protected $method = null;
    protected $path = null;
    protected $middlewares = [];
    protected $produce = [];
    protected $consume = [];
    protected $observers = [];
    protected $args = [];

    /**
     * 
     * @param Observable $observable
     */
    public function applyObservers(Observable $observable) {
        array_map(function($observer) use ($observable) {
            $obs = new $observer();
            $observable->attach($obs);
        }, $this->observers);
    }

    /**
     * 
     * @return string
     */
    public function __toString(): string {
        return 'Route ' . $this->getMethod() . '/' . $this->getPath() . ' call ' . $this->getClassName() . '->' . $this->getMethodName() . '()';
    }

}
