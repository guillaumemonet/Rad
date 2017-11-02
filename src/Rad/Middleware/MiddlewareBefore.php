<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Rad\Middleware;

use Closure;
use Rad\Http\Request;
use Rad\Http\Response;
use Rad\Middleware\MiddlewareInterface;
use Rad\Route\Route;

/**
 * Description of MiddlewareBefore
 *
 * @author Guillaume Monet
 */
abstract class MiddlewareBefore implements MiddlewareInterface {

    /**
     * 
     * @param Request $request
     * @param Response $response
     * @param Route $route
     * @param Closure $next
     * @return Closure
     */
    final public function call(Request $request, Response $response, Route $route, Closure $next) {
        $this->middle($request, $response, $route);
        return $next($request, $response, $route);
    }

}
