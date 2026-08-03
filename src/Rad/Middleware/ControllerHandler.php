<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rad\Container\Container;
use Rad\Error\Http\InternalErrorException;
use Rad\Http\HttpFactory;
use Rad\Route\Route;

/**
 * Final request handler of the pipeline: resolves the matched controller and
 * invokes its action. The current Route is read from the request attributes.
 *
 * Controller actions keep their historical signature
 * (ServerRequestInterface $request, ResponseInterface $response, array $args).
 */
final class ControllerHandler implements RequestHandlerInterface {
    public function __construct(private ?ContainerInterface $container = null) {

    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $route = $request->getAttribute(Route::class);
        if (!$route instanceof Route) {
            throw new InternalErrorException('No route bound to the request');
        }
        $className  = $route->getClassName();
        $controller = $this->container instanceof Container ? $this->container->make($className, ['route' => $route]) : new $className($route);
        $factory    = $this->container !== null ? $this->container->get(ResponseFactoryInterface::class) : new HttpFactory();
        return $controller->{$route->getMethodName()}($request, $factory->createResponse(200), $route->getArgs());
    }
}
