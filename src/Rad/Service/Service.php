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

    /**
     * 
     * @var array
     */
    protected static array $instances = [];

    /**
     * 
     * @var string|null
     */
    protected ?string $serviceType = null;

    /**
     * 
     * @var string|null
     */
    protected ?string $providedClassName = null;

    /**
     * 
     * @var type
     */
    protected $default = null;

    /**
     * 
     * @var array
     */
    protected array $services = [];

    /**
     * 
     * @var array
     */
    protected array $handlers = [];

    protected function __construct() {
        $this->serviceType = $this->getServiceType();
        if ($this->serviceType === null) {
            throw new ConfigurationException('No Handler Type returned');
        }
        $this->loadConfig();
    }

    /**
     * 
     * @return static
     */
    final public static function getInstance(): static {
        $calledClass = get_called_class();
        if (!isset(static::$instances[$calledClass])) {
            static::$instances[$calledClass] = new $calledClass();
        }
        return static::$instances[$calledClass];
    }

    private function __clone() {
        
    }

    /**
     * 
     * @param string $shortName
     * @param object $handler
     * @return void
     * @throws ServiceException
     */
    protected function addServiceHandler(string $shortName, object $handler): void {
        if ($handler instanceof $this->providedClassName) {
            $this->handlers[$shortName] = $handler;
        } else {
            throw new ServiceException('Can\'t add ' . $shortName . ' handler, doesn\'t inherit from ' . $this->providedClassName);
        }
    }

    /**
     * 
     * @param string|null $handlerType
     * @return object|null
     * @throws ServiceException
     */
    protected function getServiceHandler(?string $handlerType = null): ?object {
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

    /**
     * 
     * @param string $handlerType
     * @return bool
     */
    private function hasHandler(string $handlerType): bool {
        return isset($this->handlers[$handlerType]);
    }

    /**
     * 
     * @param string $serviceName
     * @return bool
     */
    private function hasService(string $serviceName): bool {
        return isset($this->services[$serviceName]);
    }

    /**
     * 
     * @return void
     * @throws ConfigurationException
     */
    private function loadConfig(): void {
        $config = Config::getServiceConfig($this->serviceType);

        $this->default = $config->default;
        if ($this->default === null) {
            throw new ConfigurationException('No default handler defined ' . $this->serviceType);
        }
        $this->providedClassName = $config->classname;
        if ($this->providedClassName === null) {
            throw new ConfigurationException('No Provided Class defined ' . $this->serviceType);
        }
        $this->services = (array) $config->handlers;

        array_walk($this->services, function (&$value, $key) {
            $value = $value->classname;
        });
    }

    protected abstract function getServiceType(): string;
}
