<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Collection;

/**
 * Description of Comparable
 *
 * @author Guillaume Monet
 */
interface Comparable {

    /**
     * 
     * @param object $other
     * @return int
     */
    public function compareTo(object $other): int;
}
