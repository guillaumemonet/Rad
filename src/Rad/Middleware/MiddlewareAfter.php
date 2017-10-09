<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Rad\Middleware;

use Closure;
use Rad\Api;
use Rad\Http\Request;
use Rad\Http\Response;
use Rad\Log\Log;
use Rad\Route\Route;

/**
 * Description of MiddlewareBefore
 *
 * @author Guillaume Monet
 */
abstract class MiddlewareAfter implements MiddlewareInterface {

    /**
     * 
     * @param Api $api
     * @param Closure $next
     * @return IMiddleware
     */
    final public function call(Request $request, Response $response, Route $route, Closure $next) {
        $ret = $next($request, $response, $route);
        $this->middle($request, $response, $route);
        return $ret;
    }

    abstract function middle(Request $request, Response $response, Route $route);
}
