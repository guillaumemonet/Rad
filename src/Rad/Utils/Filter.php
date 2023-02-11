<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Utils;

/**
 * Description of Filter
 *
 * @author guillaume
 */
abstract class Filter {

    private function __construct() {
        
    }

    private function __clone() {
        
    }

    /**
     * Return mathing objects with get filter
     * @param array $datas
     * @param array $get
     */
    public static function matchFilter(array &$datas, array $get) {
        if (sizeof($get) > 0) {
            $datas = array_filter($datas, function ($obj) use ($get) {
                return array_intersect_assoc((array) $obj, $get) == $get;
            });
        }
    }

    /**
     * 
     * @param array $datas
     * @param array $get
     */
    public static function containsFilter(array &$datas, array $get) {
        if (count($get) > 0) {
            $datas = array_filter($datas, function ($obj) use ($get) {
                return count(array_uintersect_assoc((array) $obj, $get, function ($a, $b) {
                            return !stristr($a, $b);
                        })) > 0;
            });
        }
    }

}
