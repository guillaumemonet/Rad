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
use Rad\Config\Config;
use Rad\Middleware\MiddlewareAfter;
use Rad\Route\Route;

/**
 * Description of Cors
 *
 * @author guillaume
 */
class Cors extends MiddlewareAfter {

    /**
     * 
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param Route $route
     * @return ResponseInterface
     */
    public function middle(ServerRequestInterface $request, ResponseInterface $response, Route $route): ResponseInterface {
        $headers = (array) Config::getApiConfig("cors");
        array_walk($headers, function (&$value, $header) use ($response) {
            $response = $response->withAddedHeader($header, $value);
        });
        if (!empty($route->getCorsDomain())) {
            return $response->withAddedHeader("Access-Control-Allow-Origin", $route->getCorsDomain());
        } else {
            return $response;
        }
    }

}
