<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Rad\Middleware;

use Closure;
use Rad\Http\Request;
use Rad\Http\Response;
use Rad\Route\Route;

/**
 * Description of MiddlewareInterface
 *
 * @author Guillaume Monet
 */
interface MiddlewareInterface {

    /**
     * 
     * @param Request $request
     * @param Response $response
     * @param Route $route
     * @param Closure $next
     */
    public function call(Request $request, Response $response, Route $route, Closure $next);

    /**
     * 
     * @param Request $request
     * @param Response $response
     * @param Route $route
     */
    public function middle(Request $request, Response $response, Route $route);
}
