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
use Rad\Session\Session;

/**
 * Description of Expose
 *
 * @author guillaume
 */
class SessionEnd extends MiddlewareAfter {

    public static $priority = 9;

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param Route $route
     * @return ResponseInterface
     */
    public function middle(ServerRequestInterface $request, ResponseInterface $response, Route $route): ResponseInterface {
        Session::getHandler()->end();
        return $response;
    }

}
