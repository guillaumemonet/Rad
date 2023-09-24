<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Middleware;

use Closure;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rad\Route\Route;

class Middleware {

    protected array $layers = [];

    public function __construct(array $layers = []) {
        $this->layers = $layers;
    }

    /**
     * 
     * @param array|Middleware|MiddlewareInterface $layers
     * @return void
     * @throws InvalidArgumentException
     */
    public function layer(array|Middleware|MiddlewareInterface $layers): void {
        if ($layers instanceof Middleware) {
            $layers = $layers->toArray();
        }
        if ($layers instanceof MiddlewareInterface) {
            $layers = [$layers];
        }
        if (!is_array($layers)) {
            throw new InvalidArgumentException(get_class($layers) . ' is not a valid middleware.');
        }
        $this->layers = array_merge($this->layers, $layers);
    }

    /**
     * 
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param Route $route
     * @param Closure $core
     * @return mixed
     */
    public function call(ServerRequestInterface $request, ResponseInterface $response, Route $route, Closure $core): mixed {
        usort($this->layers, fn($a, $b) => $a::$priority <=> $b::$priority);

        $coreFunction = fn(ServerRequestInterface $request, ResponseInterface $response, Route $route) =>
                $core(...func_get_args());

        $completeOnion = array_reduce($this->layers, fn($nextLayer, $layer) =>
                fn(ServerRequestInterface $request, ResponseInterface $response, Route $route) =>
                $layer->call($request, $response, $route, $nextLayer), $coreFunction);

        return $completeOnion($request, $response, $route);
    }

    /**
     * 
     * @return array
     */
    public function toArray(): array {
        return $this->layers;
    }
}
