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
use Rad\Middleware\MiddlewareAfter;
use Rad\Route\Route;
use Rad\Utils\Mime;

/**
 * Description of Post_SetProduce
 *
 * @author guillaume
 */
class Produce extends MiddlewareAfter {

    /**
     * 
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param Route $route
     * @return ResponseInterface
     */
    public function middle(ServerRequestInterface $request, ResponseInterface $response, Route $route): ResponseInterface {
        if (!empty($route->getProcucedMimeType())) {
            $response = $response->withAddedHeader("Content-Type", Mime::getMimeTypesFromShort(current($route->getProcucedMimeType()))[0]);
        } else {
            $response = $response->withAddedHeader("Content-Type", Mime::getMimeTypesFromShort("json")[0]);
        }
        return $response;
    }

}
