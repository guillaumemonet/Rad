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

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class Security implements RouteAttribute {

    /** @var class-string[] */
    public readonly array $securities;

    public function __construct(string ...$securities) {
        $this->securities = $securities;
    }

    public function apply(Route $route): void {
        $route->enableSecurity($this->securities);
    }
}
