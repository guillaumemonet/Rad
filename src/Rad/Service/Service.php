<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Service;

use Rad\Config\Config;
use Rad\Error\ConfigurationException;
use Rad\Error\ServiceException;

/**
 * Singleton Service
 *
 * @author guillaume
 */
abstract class Service implements ServiceInterface {

    protected static $instances  = [];
    protected $serviceType       = null;
    protected $providedClassName = null;
    protected $default           = null;
    protected $services          = [];
    protected $handlers          = [];

    protected function __construct() {
        $this->serviceType = $this->getServiceType();
        if ($this->serviceType === null) {
            throw new ConfigurationException('No Handler Type returned');
        }
        $this->loadConfig();
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
            throw new ServiceException('Can\'t add ' . $shortName . ' handler, doesn\'t inherit from ' . $this->providedClassName);
        }
    }

    protected function getServiceHandler(string $handlerType = null) {
        if ($handlerType === null || $handlerType === '' || !isset($handlerType)) {
            $handlerType = $this->default;
        }
        if (!static::hasHandler($handlerType)) {
            if (!static::hasService($handlerType)) {
                throw new ServiceException('Service ' . $handlerType . ' Not Found');
            }
            $instance                     = new $this->services[$handlerType];
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

    private function loadConfig() {
        $config = Config::getServiceConfig($this->serviceType);

        $this->default = $config->default;
        if ($this->default === null) {
            throw new ConfigurationException('No default handler defined ' . $this->serviceType);
        }
        $this->providedClassName = $config->classname;
        if ($this->providedClassName === null) {
            throw new ConfigurationException('No Provided Class defined ' . $this->serviceType);
        }
        $handlers = $config->handlers;

        $this->services = array_combine(array_keys((array) $handlers), array_map(function ($handler) {
                    return $handler->classname;
                }, (array) $handlers));
    }

    protected abstract function getServiceType(): string;
}
