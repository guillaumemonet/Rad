<?php
/*
 * The MIT License
 *
 * Copyright 2017 Guillaume Monet.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace Rad\Middleware;

use Closure;
use InvalidArgumentException;
use Rad\Api;

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
     * @return Middleware
     */
    public function layer($layers) {
        if ($layers instanceof Middleware) {
            $layers = $layers->toArray();
        }
        if ($layers instanceof IMiddleware) {
            $layers = [$layers];
        }
        if (!is_array($layers)) {
            throw new InvalidArgumentException(get_class($layers) . " is not a valid middleware.");
        }
        return new static(array_merge($this->layers, $layers));
    }

    /**
     * Run middleware around core function and pass an
     * object through it
     * @param  mixed  $object
     * @param  Closure $core
     * @return mixed         
     */
    public function call(Api $api, Closure $core) {
        $coreFunction = $this->createCoreFunction($core);
        $layers = $this->layers;
        $completeOnion = array_reduce($layers, function($nextLayer, $layer) {
            return $this->createLayer($nextLayer, $layer);
        }, $coreFunction);
        return $completeOnion($api);
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
        return function(Api $api) use($core) {
            return call_user_func($core, $api);
        };
    }

    /**
     * Get an onion layer function.
     * This function will get the object from a previous layer and pass it inwards
     * @param  IMiddleware $nextLayer
     * @param  IMiddleware $layer
     * @return Closure
     */
    private function createLayer($nextLayer, $layer) {
        return function(Api &$api) use($nextLayer, $layer) {
            return call_user_func_array([$layer, 'call'], [&$api, $nextLayer]);
        };
    }

}
