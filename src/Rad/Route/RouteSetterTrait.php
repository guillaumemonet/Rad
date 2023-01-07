<?php

namespace Rad\Route;

use Rad\Middleware\Base\AllowHeaders;
use Rad\Middleware\Base\Consume;
use Rad\Middleware\Base\Cors;
use Rad\Middleware\Base\ExposeHeaders;
use Rad\Middleware\Base\Options;
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

    public function addMiddlewares(array $middlewares) {
        $this->middlewares = array_merge($this->middlewares, $middlewares);
        return $this;
    }

    public function setObservers(array $observers) {
        $this->observers = $observers;
        return $this;
    }

    public function setProduce(array $produce) {
        $this->produce       = $produce;
        $this->middlewares[] = Produce::class;
        return $this;
    }

    public function setConsume(array $consume) {
        $this->consume       = $consume;
        $this->middlewares[] = Consume::class;
        return $this;
    }

    public function setXhr(bool $xhr = false) {
        if ($xhr) {
            $this->middlewares[] = XMLHttpRequest::class;
        }
        return $this;
    }

    public function enableCors($corsDomain = '*') {
        $this->corsDomain = $corsDomain;
        array_unshift($this->middlewares, Cors::class);
        return $this;
    }

    public function enableSession() {
        $this->sessionEnabled = true;
        return $this;
    }

    public function enableCache() {
        $this->cacheEnabled = true;
        return $this;
    }

    public function setFullPath($fullPath) {
        $this->fullPath = $fullPath;
        return $this;
    }

    public function allowHeaders(array $headers) {
        array_unshift($this->middlewares, AllowHeaders::class);
        $this->allowedHeaders = $headers;
        return $this;
    }

    public function exposeHeaders(array $headers) {
        array_unshift($this->middlewares, ExposeHeaders::class);
        $this->exposedHeaders = $headers;
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

    public function enableSecurity(array $securities) {
        return $this->addMiddlewares($securities);
    }

    public function enableOptions() {
        array_unshift($this->middlewares, Options::class);
        return $this;
    }

}
