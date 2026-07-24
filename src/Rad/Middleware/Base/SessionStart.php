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
use Rad\Session\Session;

/**
 * Starts the session before handling the request.
 */
class SessionStart extends AbstractMiddleware {

    public static int $priority = 9;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        Session::getHandler()->start();
        return $handler->handle($request);
    }
}
