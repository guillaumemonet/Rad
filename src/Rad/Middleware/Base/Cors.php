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
use Rad\Config\Config;

/**
 * Adds the configured CORS headers (and the route's allowed origin) to the
 * downstream response.
 */
class Cors extends AbstractMiddleware {

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $response = $handler->handle($request);
        foreach ((array) Config::getApiConfig('cors') as $header => $value) {
            $response = $response->withAddedHeader($header, $value);
        }
        $route = $this->route($request);
        if ($route !== null && !empty($route->getCorsDomain())) {
            $response = $response->withAddedHeader('Access-Control-Allow-Origin', $route->getCorsDomain());
        }
        return $response;
    }
}
