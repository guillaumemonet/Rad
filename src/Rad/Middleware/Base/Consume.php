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
use Rad\Error\Http\NotAcceptableException;
use Rad\Http\HttpHeaders;
use Rad\Middleware\MiddlewareBefore;
use Rad\Route\Route;
use Rad\Utils\Mime;

/**
 * Description of Consume
 *
 * @author guillaume
 */
class Consume extends MiddlewareBefore {

    /**
     * 
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param Route $route
     * @return ResponseInterface
     * @throws NotAcceptableException
     */
    public function middle(ServerRequestInterface $request, ResponseInterface $response, Route $route): ResponseInterface {
        $consumeTypes = [];
        foreach ($route->getConsumedMimeType() as $consume) {
            $consumeTypes += Mime::getMimeTypesFromShort($consume);
        }
        $acceptTypes = HttpHeaders::parseAccepted(current($request->getHeader('Accept')));
        if (isset($acceptTypes['*/*']) || sizeof(array_intersect($consumeTypes, array_keys($acceptTypes))) > 0) {
            return $response;
        } else {
            throw new NotAcceptableException('Wrong Content Type ' . implode(',', $acceptTypes) . ' Require ' . implode(' ', $consumeTypes));
        }
    }

}
