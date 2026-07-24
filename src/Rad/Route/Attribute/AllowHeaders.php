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
final class AllowHeaders implements RouteAttribute {

    /** @var string[] */
    public readonly array $headers;

    public function __construct(string ...$headers) {
        $this->headers = $headers;
    }

    public function apply(Route $route): void {
        $route->allowHeaders($this->headers);
    }
}
