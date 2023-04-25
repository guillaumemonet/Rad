<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Composer;

use Rad\Build\Build;

/**
 * Description of Manager
 *
 * @author guillaume
 */
abstract class Manager {

    public static function build($namespace = null, $path = null) {
        Build::getHandler()->build($namespace, $path);
    }

    public static function clean() {
        
    }

}
