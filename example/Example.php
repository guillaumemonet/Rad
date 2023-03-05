<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */
require(__DIR__ . "/../vendor/autoload.php");

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rad\Api;
use Rad\Build\Build;
use Rad\Controller\Controller;
use Rad\Cookie\Cookie;
use Rad\Log\Log;
use Rad\Session\Session;
use Rad\Template\Template;
use Rad\Utils\File;
use Rad\Utils\Time;

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
     * @get /build
     * @produce html
     */
    public function build(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $ret = Build::getHandler()->build();
        $response->getBody()->write($ret);
        return $response;
    }

    /**
     * @get /json/
     * @options /json/
     * @produce json
     */
    public function json(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        //$response  = $response->withAddedHeader('Hello', 'Moto');
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
     * @opts
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
     * @api 1
     * @get /session/
     * @session
     * @produce html
     */
    public function testSession(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $ret = "OLD " . Session::getHandler()->get('time') . "<br />";
        Session::getHandler()->set('time', time());
        $ret .= "New " . Session::getHandler()->get('time') . "<br />";
        $response->getBody()->write($ret);
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

    /**
     * @get /cookie/
     * @produce html
     */
    public function cookie(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write(Cookie::getHandler()->get('time'));
        Cookie::getHandler()->set("time", time());
        Cookie::getHandler()->save();

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

//Init Api
$app = new Api(__DIR__ . "/config/");

$file = new File();
$file->downloadMulti(['https://random.imagecdn.app/500/150' => __DIR__ . '/cache/test1.jpg', 'https://random.imagecdn.app/500/151' => __DIR__ . '/cache/test2.jpg'], false);

$app->addControllers(
        [Example::class]
)->run(function () {
    Log::getHandler()->debug("API REQUEST [" . round(Time::endCounter(), 10) * 1000 . "] ms");
});
