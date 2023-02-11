<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Utils;

/**
 * Description of Math
 *
 * @author Guillaume Monet
 */
final class Math {

    private function __construct() {
        
    }

    /**
     * 
     * @param float $float
     * @param int $precision
     * @return float
     */
    public static function round(float $float, int $precision = 2) {
        return round($float, $precision);
    }

}
