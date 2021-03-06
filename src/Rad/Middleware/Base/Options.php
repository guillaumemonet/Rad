<?php

/*
 * The MIT License
 *
 * Copyright 2019 guillaume.
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

namespace Rad\Middleware\Base;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rad\Config\Config;
use Rad\Middleware\MiddlewareBefore;
use Rad\Route\Route;

/**
 * Description of Options
 *
 * @author guillaume
 */
class Options extends MiddlewareBefore {

    /**
     * 
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param Route $route
     */
    public function middle(ServerRequestInterface $request, ResponseInterface $response, Route $route): ResponseInterface {
        if (strtoupper($request->getMethod()) == 'OPTIONS') {
            $defaultHeaders        = (array) Config::getApiConfig("default_response_headers");
            $defaultOptionsHeaders = (array) Config::getApiConfig("default_response_options");
            $headers               = array_merge($defaultHeaders, $defaultOptionsHeaders);
            error_log(print_r($headers, true));
            foreach ($headers as $header => $value) {
                $response = $response->withAddedHeader($header, $value);
            }
            $response->withStatus(200)->send();
            exit;
        }
        return $response;
    }

}
