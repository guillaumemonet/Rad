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
use Rad\Error\Http\ForbiddenException;

/**
 * Denies access by default: extend and override process() with the actual
 * authorization logic (call $handler->handle($request) when allowed).
 */
class Security extends AbstractMiddleware {

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        throw new ForbiddenException();
    }
}
