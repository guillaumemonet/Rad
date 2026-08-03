<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Route\Attribute;

use Rad\Route\Route;

/**
 * Contract for every route modifier attribute (Produce, Consume, Session, ...).
 *
 * HTTP verb attributes ({@see HttpMethod}) do NOT implement this interface so
 * the parser can collect modifiers and verbs separately.
 */
interface RouteAttribute {
    /**
     * Mutate the given route with this attribute's configuration.
     */
    public function apply(Route $route): void;
}
