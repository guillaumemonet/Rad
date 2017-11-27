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
 * Description of MiddlewareInterface
 *
 * @author Guillaume Monet
 */
interface MiddlewareInterface {

    /**
     * 
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param Route $route
     * @param Closure $next
     */
    public function call(ServerRequestInterface &$request, ResponseInterface &$response, Route &$route, Closure $next);

    /**
     * 
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param Route $route
     */
    public function middle(ServerRequestInterface &$request, ResponseInterface &$response, Route &$route);
}
