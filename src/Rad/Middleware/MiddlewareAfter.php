<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Rad\Middleware;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rad\Route\Route;

/**
 * Description of MiddlewareBefore
 *
 * @author Guillaume Monet
 */
abstract class MiddlewareAfter implements MiddlewareInterface {

    /**
     * 
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param Route $route
     * @param Closure $next
     * @return ResponseInterface
     */
    public function call(ServerRequestInterface $request, ResponseInterface $response, Route $route, Closure $next): ResponseInterface {
        return $this->middle($request, $next($request, $response, $route), $route);
    }

}
