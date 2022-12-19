<?php

/*
 * The MIT License
 *
 * Copyright 2017 guillaume.
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
use Rad\Error\Http\NotAcceptableException;
use Rad\Http\Header\AcceptHeader;
use Rad\Middleware\MiddlewareBefore;
use Rad\Route\Route;
use Rad\Utils\Mime;

/**
 * Description of Consume
 *
 * @author guillaume
 */
class Consume extends MiddlewareBefore {

    /**
     * 
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param Route $route
     * @return ResponseInterface
     * @throws NotAcceptableException
     */
    public function middle(ServerRequestInterface $request, ResponseInterface $response, Route $route): ResponseInterface {
        $consumeTypes = [];
        foreach ($route->getConsumedMimeType() as $consume) {
            $consumeTypes += Mime::getMimeTypesFromShort($consume);
        }
        $acceptTypes = AcceptHeader::parse(current($request->getHeader("Accept")));
        if (isset($acceptTypes['*/*']) || sizeof(array_intersect($consumeTypes, array_keys($acceptTypes))) > 0) {
            return $response;
        } else {
            throw new NotAcceptableException("Wrong Content Type " . implode(",", $acceptTypes) . " Require " . implode(" ", $consumeTypes));
        }
    }

}
