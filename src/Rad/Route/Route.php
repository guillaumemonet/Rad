<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Rad\Route;

/**
 * Description of Route
 *
 * @author guillaume
 */
class Route {

    public $version = 1;
    public $className;
    public $methodName;
    public $verb;
    public $regex;
    public $middleware = array();
    public $produce = null;
    public $consume = null;

    public function __toString() {
        return "Route " . strtoupper($this->verb) . " : /v" . $this->version . "/" . $this->regex . " call " . $this->className . "->" . $this->methodName;
    }

}
