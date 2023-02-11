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
use Rad\Error\Http\PreconditionFailedException;
use Rad\Middleware\MiddlewareBefore;
use Rad\Route\Route;

/**
 * Description of Pre_XmlHttpRequest
 *
 * @author guillaume
 */
class XMLHttpRequest extends MiddlewareBefore {

    /**
     * 
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param Route $route
     * @return ResponseInterface
     * @throws PreconditionFailedException
     */
    public function middle(ServerRequestInterface $request, ResponseInterface $response, Route $route): ResponseInterface {
        if ($request->isXhr()) {
            return $response;
        } else {
            throw new PreconditionFailedException("Must be an XmlHttpRequest");
        }
    }

}
