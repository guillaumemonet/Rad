<?php

/*
 * Copyright (C) 2016 Guillaume Monet
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Rad;

use ErrorException;
use Rad\Http\Request;
use Rad\Http\Response;
use Rad\Log\Log;
use Rad\Middleware\Middleware;
use Rad\Parser\Parser;
use Rad\Route\Router;
use Rad\Serializer\Serializer;

/**
 * Description of api_framework
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
     * @var Serializer
     */
    private $serializer = null;

    /**
     *
     * @var Parser 
     */
    private $parser = null;

    /**
     *
     * @var Request
     */
    private $request = null;

    /**
     *
     * @var Response
     */
    private $response = null;

    /**
     * 
     */
    public function __construct() {
        $this->middle = new Middleware();
        $this->router = new Router();
        $this->parser = new Parser();
        $this->serializer = new Serializer();
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
            $this->getRouter()->route($this);
        } catch (ErrorException $ex) {
            Log::error($ex->getMessage());
            $this->getResponse()->setData($ex);
        }
        $this->getResponse()->setDataType("json");
        $this->getResponse()->send();
    }

    /**
     * 
     * @return Parser
     */
    public function getParser() {
        return $this->parser;
    }

    /**
     * 
     * @return Serializer
     */
    public function getSerializer() {
        return $this->serializer;
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

}
