<?php

namespace Rad\Service;

use ErrorException;
use Rad\Config\Config;

/*
 * The MIT License
 *
 * Copyright 2017 guillaume.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Singleton Service
 *
 * @author guillaume
 */
abstract class Service implements ServiceInterface {

    protected static $instances = [];
    protected $handlerType = null;
    protected $providedClassName = null;
    protected $default = null;
    protected $services = [];
    protected $handlers = [];

    protected function __construct() {
        $this->handlerType = $this->getServiceType();
        $this->loadConfig($this->handlerType);
    }

    /**
     * 
     * @return Service
     */
    final public static function getInstance() {
        $calledClass = get_called_class();
        if (!isset(static::$instances[$calledClass])) {
            static::$instances[$calledClass] = new $calledClass();
        }
        return static::$instances[$calledClass];
    }

    final private function __clone() {
        
    }

    protected function addServiceHandler(string $shortName, $handler) {
        if ($handler instanceof $this->providedClassName) {
            $this->handlers[$shortName] = $handler;
        } else {
            throw new ErrorException('Can\'t add ' . $shortName . ' handler, doesn\'t inherit from ' . $this->providedClassName);
        }
    }

    protected function getServiceHandler(string $handlerType = null) {
        if ($handlerType === null) {
            $handlerType = $this->default;
        }
        if (!static::hasHandler($handlerType)) {
            if (!static::hasService($handlerType)) {
                throw new ErrorException('Service ' . $handlerType . ' Not Found');
            }
            $instance = new $this->services[$handlerType];
            $this->handlers[$handlerType] = $instance instanceof $this->providedClassName ? $instance : null;
        }
        return $this->handlers[$handlerType];
    }

    private function hasHandler(string $handlerType) {
        return isset($this->handlers[$handlerType]);
    }

    private function hasService(string $serviceName) {
        return isset($this->services[$serviceName]);
    }

    private function loadConfig(string $handlerType) {
        $this->handlerType = $handlerType;
        $config = Config::getServiceConfig($this->handlerType);
        $this->default = $config->default;
        $this->providedClassName = $config->classname;
        $handlers = $config->handlers;
        foreach ($handlers as $shortName => $handler) {
            $this->services[$shortName] = $handler->classname;
        }
    }

    protected abstract function getServiceType(): string;
}
