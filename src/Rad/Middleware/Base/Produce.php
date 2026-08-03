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
use Rad\Utils\Mime;

/**
 * Adds the Content-Type header matching the route's produced mime type
 * (defaults to json) to the downstream response.
 */
class Produce extends AbstractMiddleware {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $response = $handler->handle($request);
        $route    = $this->route($request);
        $produced = $route?->getProcucedMimeType() ?? [];
        $short    = !empty($produced) ? current($produced) : 'json';
        return $response->withAddedHeader('Content-Type', Mime::getMimeTypesFromShort($short)[0]);
    }
}
