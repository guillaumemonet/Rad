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

namespace Rad\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rad\Observer\Observable;
use Rad\Route\Route;
use Rad\Worker\Orderer;

/*
 * Description of Controller
 *
 * @author Guillaume Monet
 */

abstract class Controller extends Observable {

    /**
     *
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     *
     * @var ResponseInterface
     */
    protected $response;

    /**
     *
     * @var Route
     */
    protected $route;

    /**
     * 
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param Route $route
     */
    public function __construct(ServerRequestInterface $request = null, ResponseInterface $response = null, Route $route = null) {
        $this->request = $request;
        $this->response = $response;
        $this->route = $route;
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
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface {
        return $this->response;
    }

    /**
     * 
     * @return Route
     */
    public function getRoute(): Route {
        return $this->route;
    }

    /**
     * Call For an asynchronous order
     * @param type $queue
     * @param type $messageType
     * @param type $message
     */
    protected function makeOrder($queue, $messageType, $message) {
        Orderer::sendMessage($queue, $messageType, $message);
    }

}
