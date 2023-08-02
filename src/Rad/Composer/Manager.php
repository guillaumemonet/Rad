<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Composer;

use Rad\Build\Build;
use Rad\Cache\Cache;

/**
 * Description of Manager
 *
 * @author guillaume
 */
abstract class Manager {

    public static function build() {
        Build::getHandler()->build();
    }

    public static function clean() {
        Cache::getHandler()->clear();
    }
    
}
