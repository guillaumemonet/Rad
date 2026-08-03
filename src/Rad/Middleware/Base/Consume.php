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
use Rad\Error\Http\NotAcceptableException;
use Rad\Http\HttpHeaders;
use Rad\Utils\Mime;

/**
 * Rejects the request (406) when the client's Accept header does not match one
 * of the mime types the route consumes.
 */
class Consume extends AbstractMiddleware {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $route = $this->route($request);

        $consumeTypes = [];
        foreach ($route?->getConsumedMimeType() ?? [] as $consume) {
            $consumeTypes += Mime::getMimeTypesFromShort($consume);
        }
        $acceptTypes = HttpHeaders::parseAccepted(current($request->getHeader('Accept')) ?: '');
        if (isset($acceptTypes['*/*']) || count(array_intersect($consumeTypes, array_keys($acceptTypes))) > 0) {
            return $handler->handle($request);
        }
        throw new NotAcceptableException('Wrong Content Type ' . implode(',', array_keys($acceptTypes)) . ' Require ' . implode(' ', $consumeTypes));
    }
}
