<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad;

use Closure;
use ErrorException;
use Psr\Http\Message\ServerRequestInterface;
use Rad\Config\Config;
use Rad\Error\Http\NotFoundException;
use Rad\Http\Response;
use Rad\Http\ServerRequest;
use Rad\Log\Log;
use Rad\Route\RouteParser;
use Rad\Route\Router;
use Rad\Route\RouterInterface;


/**
 * 
 */
class Rad {

    const VERSION = '1.0';

    /**
     *
     * @var RouterInterface
     */
    protected $router = null;

    /**
     *
     * @var ServerRequestInterface
     */
    protected $request = null;

    /**
     *
     * @var string[]
     */
    private $controllers = [];

    /**
     *
     */
    public function __construct(string $configFilename = null) {
        Config::load($configFilename);
        $routerClass        = Config::getApiConfig('router');
        $serverRequestClass = Config::getApiConfig('serverrequest');
        $this->router       = $routerClass !== null ? new $routerClass : new Router();
        $this->request      = $serverRequestClass !== null ? $serverRequestClass::fromGlobals() : ServerRequest::fromGlobals();
    }

    /**
     * 
     * @param Closure $finalClosure
     */
    public final function run(Closure $finalClosure = null) {
        try {
            if (!$this->getRouter()->load()) {
                $this->getRouter()->setRoutes(RouteParser::parseRoutes($this->getControllers()));
                $this->getRouter()->save();
            }
            $response = $this->getRouter()->route($this->request);
            $response->send();
        } catch (ErrorException $ex) {
            Log::getHandler()->error($ex->getMessage());
            $response = new Response($ex->getCode());
            $response->getBody()->write($ex->getCode() . ' ' . $ex->getMessage());
            $response->send();
        } finally {
            if ($finalClosure != null) {
                call_user_func_array($finalClosure, []);
            }
        }
    }

    /**
     *
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface {
        if ($this->router !== null) {
            return $this->router;
        } else {
            throw new NotFoundException("RouterInterface Not Defined");
        }
    }

    /**
     *
     * @param RouterInterface $routeur
     */
    public function setRouter(RouterInterface $routeur): self {
        $this->routeur = $routeur;
        return $this;
    }

    /**
     *
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface {
        return $this->request;
    }

    /**
     *
     * @param string[] $controllers
     * @return $this
     */
    public function addControllers(array $controllers): self {
        $this->controllers += $controllers;
        return $this;
    }

    /**
     *
     * @param string[] $controllers
     * @return $this
     */
    public function setControllers(array $controllers): self {
        $this->controllers = $controllers;
        return $this;
    }

    /**
     *
     * @param string $controller
     * @return $this
     */
    public function addController(string $controller): self {
        $this->controllers[] = $controller;
        return $this;
    }

    /**
     *
     * @return string[]
     */
    public function getControllers(): array {
        return $this->controllers;
    }

}
