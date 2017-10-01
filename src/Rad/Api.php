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
use Rad\Codec\Codec;
use Rad\Http\Request;
use Rad\Http\Response;
use Rad\Log\Log;
use Rad\Middleware\Middleware;
use Rad\Route\RouteParser;
use Rad\Route\Router;

/**
 * Description of Api
 *
 * @author Guillaume Monet
 */
abstract class Api {

    const VERSION = '1.0';

    /**
     *
     * @var Middleware
     */
    private $middle = null;

    /**
     *
     * @var Router
     */
    private $router = null;

    /**
     *
     * @var RequestInterface
     */
    private $request = null;

    /**
     *
     * @var ResponseInterface
     */
    private $response = null;

    /**
     *
     * @var Codec
     */
    private $codec = null;

    /**
     * 
     */
    public function __construct() {
        $this->middle = new Middleware();
        $this->codec = new Codec();
        $this->router = new Router();
        $this->request = new Request();
        $this->response = new Response();
        if (!$this->router->load()) {
            $this->router->set(RouteParser::parseRoutes($this->addRoutes()));
            $this->router->save();
        }
        Log::getLogHandler()->debug($this->router);
    }

    /**
     * 
     * @return type
     * @throws ErrorException
     */
    public final function run() {
        try {
            $this->getRouter()->route($this);
        } catch (ErrorException $ex) {
            Log::getLogHandler()->error($ex->getMessage());
            $this->getResponse()->headerStatus($ex->getCode());
            $this->getResponse()->setDataType("json");
            $this->getResponse()->setData($ex);
        }
        $this->getResponse()->send();
    }

    /**
     * 
     * @return Codec
     */
    public function getCodec() {
        return $this->codec;
    }

    /**
     * 
     * @return Router
     */
    public function getRouter() {
        return $this->router;
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

    /**
     * 
     * @return Middleware
     */
    public function getMiddleware() {
        return $this->middle;
    }

    public abstract function addRoutes(): array;
}
