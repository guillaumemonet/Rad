<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Rad\Route;

/**
 * Description of RouteInterface
 *
 * @author Guillaume Monet
 */
interface RouteInterface {

    public function route($method, $uri, DeferedCallable $method_name);
}
