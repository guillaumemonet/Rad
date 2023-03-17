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
use Rad\Middleware\MiddlewareBefore;
use Rad\Route\Route;

/**
 * Description of Options
 *
 * @author guillaume
 */
class Options extends MiddlewareBefore {

    /**
     * 
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param Route $route
     */
    public function middle(ServerRequestInterface $request, ResponseInterface $response, Route $route): ResponseInterface {
        if (strtoupper($request->getMethod()) == 'OPTIONS') {
            $headers = (array) Config::getApiConfig('cors');
            array_walk($headers, function (&$value, $header) use (&$response) {
                $response = $response->withAddedHeader($header, $value);
            });
            $response->withStatus(200)->send();
            exit;
        } else {
            return $response;
        }
    }

}
