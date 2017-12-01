<?php

namespace Rad\Route;

use Rad\Middleware\Base\Consume;
use Rad\Middleware\Base\Produce;
use Rad\Middleware\Base\XMLHttpRequest;

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

    public function setMethod(string $method) {
        $this->method = strtoupper($method);
        return $this;
    }

    public function setPath(string $path) {
        $this->path = $path;
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

    public function setProduce(array $produce) {
        $this->produce = $produce;
        $this->middlewares[] = Produce::class;
        return $this;
    }

    public function setConsume(array $consume) {
        $this->consume = $consume;
        $this->middlewares[] = Consume::class;
        return $this;
    }

    public function setXhr($xhr) {
        $this->middlewares[] = XMLHttpRequest::class;
        return $this;
    }

    /**
     * 
     * @param array $args
     * @return $this
     */
    public function setArgs(array $args) {
        $this->args = $args;
        return $this;
    }

}
