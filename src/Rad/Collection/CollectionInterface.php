<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Collection;

use ArrayAccess;
use Countable;
use IteratorAggregate;

/**
 * Description of CollectionInterface
 *
 * @author guillaume
 */
interface CollectionInterface extends ArrayAccess, Countable, IteratorAggregate {

    public function set(string $key, $value);

    public function get(string $key, $default = null);

    public function replace(array $items);

    public function all();

    public function has(string $key): bool;

    public function remove(string $key);

    public function clear();
}
