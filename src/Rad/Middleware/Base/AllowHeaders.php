<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Middleware\Base;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Advertises the route's allowed request headers via
 * Access-Control-Allow-Headers.
 */
class AllowHeaders extends AbstractMiddleware {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $response = $handler->handle($request);
        $route    = $this->route($request);
        if ($route === null) {
            return $response;
        }
        return $response->withAddedHeader('Access-Control-Allow-Headers', $route->getAllowedHeaders());
    }
}
