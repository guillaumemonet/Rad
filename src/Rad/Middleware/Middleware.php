<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Rad\Middleware;

use Closure;
use InvalidArgumentException;
use Rad\Api;
use Rad\Http\Request;
use Rad\Http\Response;
use Rad\Route\Route;

/**
 * Description of Middleware
 *
 * @author Guillaume Monet
 */
final class Middleware {

    private $layers;

    public function __construct(array $layers = []) {
        $this->layers = $layers;
    }

    /**
     * Add layer(s) or Middleware
     * @param  mixed $layers
     */
    public function layer($layers) {
        if ($layers instanceof Middleware) {
            $layers = $layers->toArray();
        }
        if ($layers instanceof MiddlewareInterface) {
            $layers = [$layers];
        }
        if (!is_array($layers)) {
            throw new InvalidArgumentException(get_class($layers) . " is not a valid middleware.");
        }
        $this->layers = array_merge($this->layers, $layers);
    }

    /**
     * Run middleware around core function and pass an
     * object through it
     * @param  Api  $api
     * @param  Closure $core
     * @return mixed         
     */
    public function call(Request $request, Response $response, Route $route, Closure $core) {
        $coreFunction = $this->createCoreFunction($core);
        $layers = $this->layers;
        $completeOnion = array_reduce($layers, function($nextLayer, $layer) {
            return $this->createLayer($nextLayer, $layer);
        }, $coreFunction);
        return $completeOnion($request, $response, $route);
    }

    /**
     * Get the layers of this onion, can be used to merge with another onion
     * @return array
     */
    public function toArray() {
        return $this->layers;
    }

    /**
     * The inner function of the onion.
     * This function will be wrapped on layers
     * @param  Closure $core the core function
     * @return Closure
     */
    private function createCoreFunction(Closure $core) {
        return function(Request $request, Response $response, Route $route) use($core) {
            return call_user_func($core, $request, $response, $route);
        };
    }

    /**
     * Get an onion layer function.
     * This function will get the object from a previous layer and pass it inwards
     * @param  MiddlewareInterface $nextLayer
     * @param  IMiddlewareInterface $layer
     * @return Closure
     */
    private function createLayer($nextLayer, $layer) {
        return function(Request $request, Response $response, Route $route) use($nextLayer, $layer) {
            return call_user_func_array([$layer, 'call'], [$request, $response, $route, $nextLayer]);
        };
    }

}
