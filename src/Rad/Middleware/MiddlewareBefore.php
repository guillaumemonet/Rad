<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Middleware;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param Route $route
     * @param Closure $next
     * @return ResponseInterface
     */
    public function call(ServerRequestInterface $request, ResponseInterface $response, Route $route, Closure $next): ResponseInterface {
        return $next($request, $this->middle($request, $response, $route), $route);
    }

}
