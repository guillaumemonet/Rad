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
use Rad\Error\Http\ForbiddenException;
use Rad\Middleware\MiddlewareBefore;
use Rad\Route\Route;

/**
 * Description of Security
 *
 * @author guillaume
 */
class Security extends MiddlewareBefore {

    /**
     * By default forbidden all must be overridden
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param Route $route
     * @throws ForbiddenException
     */
    public function middle(ServerRequestInterface $request, ResponseInterface $response, Route $route): ResponseInterface {
        throw new ForbiddenException();
    }

}
