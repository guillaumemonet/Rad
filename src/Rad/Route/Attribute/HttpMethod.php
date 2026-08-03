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
 * Base class for HTTP verb attributes (#[Get], #[Post], ...).
 *
 * Each concrete verb is repeatable on a method so a single action can answer
 * several verbs/paths.
 */
abstract class HttpMethod {
    public function __construct(public readonly string $path) {

    }

    /**
     * The HTTP verb this attribute maps to (GET, POST, ...).
     */
    abstract public function verb(): string;

    public function apply(Route $route): void {
        $route->setMethod($this->verb())->setPath($this->path);
    }
}
