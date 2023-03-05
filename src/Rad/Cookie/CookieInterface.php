<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Cookie;

/**
 * Description of SessionInterface
 *
 * @author guillaume
 */
interface CookieInterface {

    public function get(string $index);

    public function set(string $index, $value);

    public function has(string $index): bool;

    public function save(): bool;
}
