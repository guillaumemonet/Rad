<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Rad\Route;

use Rad\Middleware\Base\Post_SetProduce;
use Rad\Middleware\Base\Pre_CheckConsume;
use Rad\Observer\Observable;

/**
 * Description of Route
 *
 * @author guillaume
 */
class Route {

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

    public function __toString() {
        return "Route " . strtoupper($this->verb) . " : /v" . $this->version . "/" . $this->regex . " call " . $this->className . "->" . $this->methodName;
    }

    public function setVersion(string $version) {
        $this->version = $version;
        return $this;
    }

    public function setClassName(string $className) {
        $this->className = $className;
        return $this;
    }

    public function setMethodName(string $methodName) {
        $this->methodName = $methodName;
        return $this;
    }

    public function setVerb(string $verb) {
        $this->verb = strtolower($verb);
        return $this;
    }

    public function setRegExp(string $regExp) {
        $this->regex = $regExp;
        return $this;
    }

    public function setMiddlewares(array $middlewares) {
        $this->middlewares = $middlewares;
        return $this;
    }

    public function setObservers(array $observers) {
        $this->observers = $observers;
        return $this;
    }

    public function setProduce(string $produce) {
        $this->produce = strtolower($produce);
        return $this;
    }

    public function setConsume(string $consume) {
        $this->consume = strtolower($consume);
        return $this;
    }

    /**
     * 
     * @return array
     */
    public function getMiddlewares() {
        $ret = array();
        foreach ($this->middlewares as $middle) {
            $ret[] = new $middle();
        }
        $ret[] = new Pre_CheckConsume();
        $ret[] = new Post_SetProduce();
        return $ret;
    }

    public function getVerb() {
        return $this->verb;
    }

    public function getClassName() {
        return $this->className;
    }

    public function getMethodName() {
        return $this->methodName;
    }

    public function getRegExp() {
        return $this->regex;
    }

    public function getProcucedMimeType() {
        return $this->produce;
    }

    public function getConsumedMimeType() {
        return $this->consume;
    }

    public function getVersion() {
        return $this->version;
    }

    public function getObservers() {
        return $this->observers;
    }

    public function applyObservers(Observable $observable) {
        array_map(function($observer) use ($observable) {
            $obs = new $observer();
            $observable->attach($obs);
        }, $this->observers);
    }

    /**
     * 
     * @param array $args
     * @return $this
     */
    public function setArgs(array $args) {
        array_shift($args);
        $this->args = $args;
        return $this;
    }

    /**
     * 
     * @return array
     */
    public function getArgs(): array {
        return $this->args;
    }

}
