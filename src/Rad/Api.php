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

use ErrorException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Rad\Http\Request;
use Rad\Http\Response;
use Rad\Log\Log;
use Rad\Route\RouteParser;
use Rad\Route\Router;
use Rad\Route\RouterInterface;

/**
 * Description of Api
 *
 * @author Guillaume Monet
 */
abstract class Api {

    const VERSION = '1.0';

    /**
     *
     * @var RouterInterface
     */
    protected $router = null;

    /**
     *
     * @var RequestInterface
     */
    protected $request = null;

    /**
     *
     * @var ResponseInterface
     */
    protected $response = null;

    /**
     * 
     */
    public function __construct() {
        $this->router = new Router();
        $this->request = new Request();
        $this->response = new Response();
    }

    /**
     * 
     * @return type
     * @throws ErrorException
     */
    public final function run() {
        try {
            if (!$this->getRouter()->load()) {
                $this->getRouter()->setRoutes(RouteParser::parseRoutes($this->addControllers()));
                $this->getRouter()->save();
            }
            $this->getRouter()->route(
                    $this->getRequest()
                    , $this->getResponse()
            );
        } catch (ErrorException $ex) {
            Log::getHandler()->error($ex->getMessage());
            $this->getResponse()->headerStatus($ex->getCode());
            $this->getResponse()->setDataType($this->getRequest()->accept_type);
            $this->getResponse()->setData($ex);
        }
        $this->getResponse()->send();
    }

    /**
     * 
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface {
        return $this->router;
    }

    /**
     * 
     * @param RouterInterface $routeur
     */
    public function setRouter(RouterInterface $routeur) {
        $this->routeur = $routeur;
    }

    /**
     * 
     * @return Request
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * 
     * @return Response
     */
    public function getResponse() {
        return $this->response;
    }

    public abstract function addControllers(): array;
}
