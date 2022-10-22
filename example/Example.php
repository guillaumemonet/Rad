<?php

require(__DIR__ . "/../vendor/autoload.php");

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rad\Api;
use Rad\Controller\Controller;
use Rad\Log\Log;
use Rad\Utils\Time;

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

/**
 * Simple example for testing purpose
 *
 * @author guillaume
 * @RestController
 */
class Example extends Controller {

    /**
     * @get /
     * @produce html
     */
    public function helloWorld(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write("<b>Hello World</b>");
        return $response;
    }

    /**
     * @get /json/
     * @produce json
     */
    public function jsonHelloWorld(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response  = $response->withAddedHeader('Hello', 'Moto');
        $std       = new stdClass();
        $std->toto = "toto/fdsf   sdf://";
        $std->arr  = ["toto ", "titi"];
        $response->getBody()->write(json_encode([$std, $std]));
        return $response;
    }

    /**
     * @api 1
     * @get /helloworld/(?<name>[aA-zZ]*)/display/(?<welcome>.*)/
     * @produce html
     */
    public function namedHelloWorld(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write('<b>Hello World</b> ' . $args['name'] . " to " . $args['welcome']);
        return $response;
    }

    /**
     * @api 1
     * @get /server/
     * @cors
     * @produce json
     */
    public function serverRequest(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write(json_encode($request->getHeaders()));
        return $response;
    }

}

$time = Time::startCounter();
$app  = new Api(__DIR__ . "/config/");
$app->addControllers([Example::class])
        ->run(function () {
            $ltime = Time::endCounter();
            Log::getHandler()->debug("API REQUEST [" . round($ltime, 10) * 1000 . "] ms");
        });
