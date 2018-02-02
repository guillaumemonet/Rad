<?php

/*
 * The MIT License
 *
 * Copyright 2017 Guillaume Monet.
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

namespace Rad;

use Closure;
use ErrorException;
use Psr\Http\Message\ServerRequestInterface;
use Rad\Config\Config;
use Rad\Controller\Controller;
use Rad\Error\Http\NotFoundException;
use Rad\Http\Response;
use Rad\Http\ServerRequest;
use Rad\Log\Log;
use Rad\Route\RouteParser;
use Rad\Route\Router;
use Rad\Route\RouterInterface;

/**
 * Description of Api
 *
 * @author Guillaume Monet
 */
class Api {

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
     * @var Controller[]
     */
    private $controllers = [];

    /**
     *
     */
    public function __construct(string $configFilename = null) {
        if ($configFilename !== null) {
            Config::load($configFilename);
        } else {
            Config::load(__DIR__ . '/../../config/config.default.json');
        }
        $router = Config::getApiConfig('router');
        $serverRequest = Config::getApiConfig('serverrequest');
        $this->router = $router !== null ? new $router : new Router();
        $this->request = $serverRequest !== null ? new $serverRequest : new ServerRequest();
    }

    /**
     * 
     * @param Closure $finalClosure
     */
    public final function run(Closure $finalClosure = null) {
        try {
            $this->checkOptionsCors();
            if (!$this->getRouter()->load()) {
                $this->getRouter()->setRoutes(RouteParser::parseRoutes($this->getControllers()));
                $this->getRouter()->save();
            }
            $response = $this->getRouter()->route($this->request);
            $response->send();
        } catch (ErrorException $ex) {
            Log::getHandler()->error($ex->getMessage());
            $response = new Response($ex->getCode());
            $response->setDataType($this->getRequest()->getHeader("Accept-Type"));
            $response->getBody()->write($ex);
            $response->send();
        } finally {
            if ($finalClosure != null) {
                call_user_func_array($finalClosure);
            }
        }
    }

    private function checkOptionsCors() {
        if (Config::getApiConfig('response_to_options') &&
                ($this->request->getHeader('REQUEST_METHOD') == 'OPTIONS') && (
                $this->request->getHeader('HTTP_ACCESS_CONTROL_REQUEST_METHOD') &&
                in_array($this->request->getHeader('HTTP_ACCESS_CONTROL_REQUEST_METHOD'), ['POST', 'DELETE', 'PUT', 'GET'])
                )
        ) {
            $response = new Response(200, 'OK', (array) Config::getApiConfig('default_response_options'));
            $response->send();
            exit;
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
    public function setRouter(RouterInterface $routeur) {
        $this->routeur = $routeur;
        return $this;
    }

    /**
     *
     * @return ServerRequestInterface
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     *
     * @param array $controllers
     * @return $this
     */
    public function addControllers(array $controllers) {
        $this->controllers += $controllers;
        return $this;
    }

    /**
     *
     * @param array $controllers
     * @return $this
     */
    public function setControllers(array $controllers) {
        $this->controllers = $controllers;
        return $this;
    }

    /**
     *
     * @param Controller $controller
     * @return $this
     */
    public function addController($controller) {
        $this->controllers[] = $controller;
        return $this;
    }

    /**
     *
     * @return Controller[]
     */
    public function getControllers() {
        return $this->controllers;
    }

}
