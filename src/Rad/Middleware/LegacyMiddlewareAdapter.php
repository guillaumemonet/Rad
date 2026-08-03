<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface as PsrMiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rad\Http\HttpFactory;
use Rad\Route\Route;

/**
 * Runs a legacy Rad middleware (call()/middle() with a threaded Response) inside
 * a PSR-15 pipeline.
 *
 * Note: the legacy model threads a Response forward through "before"
 * middlewares; PSR-15 does not. "After" middlewares (which transform the
 * downstream response) are fully preserved; "before" middlewares keep their
 * checks/short-circuits (exceptions) but any pre-population of the response is
 * discarded, since the downstream handler builds its own.
 */
final class LegacyMiddlewareAdapter implements PsrMiddlewareInterface {
    public function __construct(private MiddlewareInterface $middleware) {

    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $route = $request->getAttribute(Route::class);
        $next  = static fn (ServerRequestInterface $req, ResponseInterface $res, ?Route $rt = null): ResponseInterface => $handler->handle($rt !== null ? $req->withAttribute(Route::class, $rt) : $req);

        return $this->middleware->call($request, (new HttpFactory())->createResponse(200), $route, $next);
    }
}
