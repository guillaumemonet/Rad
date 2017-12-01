<?php

namespace Rad\Route;

/**
 * Description of RouteGetterTrait
 *
 * @author guillaume
 */
trait RouteGetterTrait {

    /**
     * 
     * @return array
     */
    public function getMiddlewares() {
        $ret = [];
        foreach ($this->middlewares as $middle) {
            $ret[] = new $middle();
        }
        return $ret;
    }

    public function getMethod(): string {
        return strtoupper($this->method);
    }

    public function getClassName(): string {
        return $this->className;
    }

    public function getMethodName(): string {
        return $this->methodName;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function getProcucedMimeType(): array {
        return $this->produce;
    }

    public function getConsumedMimeType(): array {
        return $this->consume;
    }

    public function getVersion() {
        return $this->version;
    }

    public function getObservers(): array {
        return $this->observers;
    }

    /**
     * 
     * @return array
     */
    public function getArgs(): array {
        return $this->args;
    }

}
