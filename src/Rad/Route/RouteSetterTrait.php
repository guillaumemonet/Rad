<?php

namespace Rad\Route;

/**
 * Description of RouteSetterTrait
 *
 * @author guillaume
 */
trait RouteSetterTrait {

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
     * @param array $args
     * @return $this
     */
    public function setArgs(array $args) {
        array_shift($args);
        $this->args = $args;
        return $this;
    }

}
