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
use Rad\Build\Build;
use Rad\Controller\Controller;
use Rad\Cookie\Cookie;
use Rad\Event\Event;
use Rad\Event\EventHandler;
use Rad\Log\Log;
use Rad\Rad;
use Rad\Route\Attribute\Consume;
use Rad\Route\Attribute\Get;
use Rad\Route\Attribute\Options;
use Rad\Route\Attribute\Produce;
use Rad\Route\Attribute\Session;
use Rad\Route\Attribute\Version;
use Rad\Session\Session as SessionService;
use Rad\Template\Template;
use Rad\Utils\File;
use Rad\Utils\Time;

/**
 * Simple example for testing purpose
 *
 * @author guillaume
 */
class Example extends Controller {

    public $state = 1;

    #[Get('/'), Produce('html')]
    public function html(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write("<b>Hello World</b>");
        return $response;
    }

    #[Get('/build'), Produce('html')]
    public function build(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $ret = Build::getHandler()->build();
        $response->getBody()->write($ret);
        return $response;
    }

    #[Get('/info'), Produce('html')]
    public function info(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        phpinfo();
        exit;
    }

    #[Get('/json/'), Options('/json/'), Produce('json')]
    public function json(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $std       = new stdClass();
        $std->toto = "toto/fdsf   sdf://";
        $std->arr  = ["toto ", "titi"];
        $response->getBody()->write(json_encode([$std, $std]));
        return $response;
    }

    #[Version(1), Get('/helloworld/(?<name>[aA-zZ]*)/display/(?<welcome>.*)/'), Produce('html')]
    public function htmlWithArgs(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write('<b>Hello World</b> ' . $args['name'] . " to " . $args['welcome']);
        return $response;
    }

    #[Version(1), Get('/server/'), Produce('json')]
    public function serverRequest(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write(json_encode($request->getHeaders()));
        return $response;
    }

    #[Version(1), Get('/consume/'), Consume('html'), Produce('json')]
    public function testConsume(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write(json_encode($request->getHeaders()));
        return $response;
    }

    #[Version(1), Get('/session/'), Session, Produce('html')]
    public function testSession(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $ret = "OLD " . SessionService::getHandler()->get('time') . "<br />";
        SessionService::getHandler()->set('time', time());
        $ret .= "New " . SessionService::getHandler()->get('time') . "<br />";
        $response->getBody()->write($ret);
        return $response;
    }

    #[Get('/template/'), Produce('html')]
    public function template(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response = $response->withAddedHeader('Hello', 'Moto');
        if (!Template::getHandler()->isCached("index.tpl", "cached", "compiled")) {
            $file = new File();
            $file->downloadMulti(['https://random.imagecdn.app/500/150' => __DIR__ . '/cache/test1.jpg', 'https://random.imagecdn.app/500/151' => __DIR__ . '/cache/test2.jpg'], false);
            Log::getHandler()->debug("Not Cached index.tpl");
            Template::getHandler()->assign("img1", 'example/cache/test1.jpg');
            Template::getHandler()->assign("img2", 'example/cache/test2.jpg');
        }
        $html = Template::getHandler()->fetch("index.tpl", "cached", "compiled");
        $response->getBody()->write($html);
        return $response;
    }

    #[Get('/observer/'), Produce('html')]
    public function observer(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $this->state = 2;
        $this->dispatch(new StateChangedEvent($this->state));
        $response->getBody()->write("State Change");
        return $response;
    }

    #[Get('/test/large/(?<name>[aA-zZ]*)/one/'), Produce('html')]
    public function pathOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write("Path One");
        return $response;
    }

    #[Get('/test/large/(?<name>[aA-zZ]*)/two/'), Produce('html')]
    public function pathTwo(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write("Path Two");
        return $response;
    }

    #[Get('/cookie/'), Produce('html')]
    public function cookie(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $datas = Cookie::getHandler()->get('time');
        $response->getBody()->write($datas ? (string) $datas : "");
        Cookie::getHandler()->set("time", time());
        Cookie::getHandler()->save();
        return $response;
    }

}

//Pass through for pictures,docs,fonts
$extensions = ["php", "jpg", "jpeg", "gif", "css", "webp", "webm", "png", "svg", "ico", "ttf", "woff", "woff2", "js", "map", "eot"];

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$ext  = pathinfo($path, PATHINFO_EXTENSION);
if (in_array($ext, $extensions)) {
    return false;
}

Time::startCounter();
/**
 * Load the example event + listener
 */
require(__DIR__ . '/StateChangedEvent.php');
require(__DIR__ . '/TestObserver.php');

//Init Api
$app = new Rad(__DIR__ . "/config/");

// Register the PSR-14 listener for the example event.
$dispatcher = Event::getHandler();
if ($dispatcher instanceof EventHandler) {
    $dispatcher->addListener(StateChangedEvent::class, new TestObserver());
}

$app->addControllers(
        [Example::class]
)->run(function () {
    Log::getHandler()->debug("API REQUEST [" . round(Time::endCounter(), 10) * 1000 . "] ms");
});
