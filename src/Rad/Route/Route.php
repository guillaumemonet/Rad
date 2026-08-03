<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Route;

use Rad\Observer\Observable;

/**
 * Description of Route
 *
 */
class Route {
    use RouteSetterTrait;
    use RouteGetterTrait;

    protected int|string $version = 1;
    protected ?string $className  = null;
    protected ?string $methodName = null;
    protected ?string $method     = null;
    protected ?string $path       = null;
    /** @var array<int, class-string> */
    protected array $middlewares = [];
    /** @var string[] */
    protected array $produce = [];
    /** @var string[] */
    protected array $consume = [];
    /** @var array<int, class-string> */
    protected array $observers = [];
    /** @var array<string, string> */
    protected array $args          = [];
    protected bool $sessionEnabled = false;
    protected bool $cacheEnabled   = false;
    protected ?string $fullPath    = null;
    /** @var string[] */
    protected array $allowedHeaders = [];
    /** @var string[] */
    protected array $exposedHeaders = [];
    protected string $corsDomain    = '*';

    /**
     *
     * @param Observable $observable
     */
    public function applyObservers(Observable $observable) {
        array_map(function ($observer) use ($observable) {
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
