<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Utils;

/**
 * Description of Sort
 *
 * @author Guillaume Monet
 */
class Sort {

    private $order;
    private $order_by;

    const DESC = -1;
    const ASC  = 1;

    public function __construct($order_by, $order = Sort::ASC) {
        $this->order    = $order;
        $this->order_by = $order_by;
    }

    /**
     * 
     * @param type $a
     * @param type $b
     * @return int
     */
    public function sort($a, $b) {
        $field = $this->order_by;
        if ($a->{$field} == $b->{$field}) {
            return 0;
        }
        return $this->order * (($a->{$field} < $b->{$field}) ? -1 : 1);
    }

    /**
     * 
     * @param array $array
     * @param type $order_by
     * @param type $order
     * @return bool
     */
    public static function sortBy(array &$array, $order_by, $order = Sort::ASC): bool {
        $sort = new Sort($order_by, $order);
        return uasort($array, array($sort, "sort"));
    }

}
