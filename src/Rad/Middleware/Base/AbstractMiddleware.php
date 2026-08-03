<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Middleware\Base;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Rad\Route\Route;

/**
 * Base class for the framework's built-in PSR-15 middlewares.
 *
 * $priority orders the pipeline (lower runs first / outermost); the current
 * Route is exposed to subclasses through {@see self::route()}.
 */
abstract class AbstractMiddleware implements MiddlewareInterface {
    public static int $priority = 1;

    protected function route(ServerRequestInterface $request): ?Route {
        $route = $request->getAttribute(Route::class);
        return $route instanceof Route ? $route : null;
    }
}
