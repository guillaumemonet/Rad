<?php

require(__DIR__ . "/../vendor/autoload.php");

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rad\Api;
use Rad\Config\AutoConfig;
use Rad\Controller\Controller;
use Rad\Log\Log;
use Rad\Template\Template;
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
 * @Controller
 */
class Example extends Controller {

    public $state = 1;

    /**
     * @get /
     * @produce html
     */
    public function html(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write("<b>Hello World</b>");
        return $response;
    }

    /**
     * @get /json/
     * @produce json
     */
    public function json(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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
    public function htmlWithArgs(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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

    /**
     * @api 1
     * @get /consume/
     * @consume html
     * @produce json
     */
    public function testConsume(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write(json_encode($request->getHeaders()));
        return $response;
    }

    /**
     * @get /template/
     * @produce html
     */
    public function template(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response = $response->withAddedHeader('Hello', 'Moto');
        if (!Template::getHandler()->isCached("index.tpl", "cached", "compiled")) {
            Log::getHandler()->debug("Not Cached index.tpl");
            Template::getHandler()->assign("img1", 'example/cache/test1.jpg');
            Template::getHandler()->assign("img2", 'example/cache/test2.jpg');
        }
        $html = Template::getHandler()->fetch("index.tpl", "cached", "compiled");
        $response->getBody()->write($html);
        return $response;
    }

    /**
     * @get /observer/
     * @produce html
     * @observer \TestObserver
     */
    public function observer(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write("State Change");
        $this->state = 2;
        $this->notify();
        return $response;
    }

    /**
     * @get /test/large/(?<name>[aA-zZ]*)/one/
     * @produce html
     */
    public function pathOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write("Path One");
        return $response;
    }

    /**
     * @get /test/large/(?<name>[aA-zZ]*)/two/
     * @produce html
     */
    public function pathTwo(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write("Path Two");
        return $response;
    }

}

//Pass through for pictures and docs
$extensions = array("php", "jpg", "jpeg", "gif", "css", "webp", "webm", "png", "svg");

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$ext  = pathinfo($path, PATHINFO_EXTENSION);
if (in_array($ext, $extensions)) {
    return false;
}

Time::startCounter();
/**
 * Load TestObserver class
 */
require(__DIR__ . '/TestObserver.php');

$file = new Rad\Utils\File();
$file->downloadMulti(['https://random.imagecdn.app/500/150' => __DIR__ . '/cache/test1.jpg', 'https://random.imagecdn.app/500/151' => __DIR__ . '/cache/test2.jpg'], false);

$app = new Api(__DIR__ . "/config/");
$app->addControllers(
        AutoConfig::loadControllers()
)->run(function () {
    Log::getHandler()->debug("API REQUEST [" . round(Time::endCounter(), 10) * 1000 . "] ms");
});
