<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Rad\Middleware;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rad\Http\Request;
use Rad\Http\Response;
use Rad\Route\Route;

/**
 * Description of MiddlewareBefore
 *
 * @author Guillaume Monet
 */
abstract class MiddlewareAfter implements MiddlewareInterface {

    /**
     * 
     * @param Request $request
     * @param Response $response
     * @param Route $route
     * @param Closure $next
     * @return Closure
     */
    final public function call(ServerRequestInterface &$request, ResponseInterface &$response, Route &$route, Closure $next) {
        $ret = $next($request, $response, $route);
        $this->middle($request, $response, $route);
        return $ret;
    }

}
