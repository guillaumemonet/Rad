<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

/**
 * Quick debug var.
 * @param mixed $var
 */
function qd($var) {
    error_log(print_r($var, true));
}

/**
 * Quicl debug var in html format to standard output
 * @param mixed $var
 */
function qdh($var) {
    print_f('<pre>%s</pre>', print_r($var, true));
}
