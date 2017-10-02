<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Rad\Middleware;

use Closure;
use Rad\Api;

/**
 * Description of MiddlewareInterface
 *
 * @author Guillaume Monet
 */
interface MiddlewareInterface {

    /**
     * 
     * @param Api $api
     * @param Closure $next
     */
    public function call(Api $api, Closure $next);

    /**
     * 
     * @param Api $api
     */
    public function middle(Api $api);
}
