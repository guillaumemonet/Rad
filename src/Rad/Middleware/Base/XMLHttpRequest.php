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
use Rad\Error\Http\PreconditionFailedException;

/**
 * Enforces that the request is an XMLHttpRequest.
 */
class XMLHttpRequest extends AbstractMiddleware {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        if (method_exists($request, 'isXhr') && $request->isXhr()) {
            return $handler->handle($request);
        }
        throw new PreconditionFailedException('Must be an XmlHttpRequest');
    }
}
