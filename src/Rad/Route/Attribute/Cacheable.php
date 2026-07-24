<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Route\Attribute;

use Attribute;
use Rad\Route\Route;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class Cacheable implements RouteAttribute {

    public function apply(Route $route): void {
        $route->enableCache();
    }
}
