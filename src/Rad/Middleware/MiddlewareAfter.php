<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Rad\Middleware;

use Closure;
use Rad\Api;

/**
 * Description of AMiddlewareBefore
 *
 * @author Guillaume Monet
 */
abstract class MiddlewareAfter implements MiddlewareInterface {

    /**
     * 
     * @param Api $api
     * @param Closure $next
     * @return IMiddleware
     */
    final public function call(Api &$api, Closure $next) {
        $ret = $next($api);
        $this->middle($api);
        return $ret;
    }

    abstract function middle(Api &$api);
}
