<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Http;

/**
 * Description of Buffer
 *
 * @author guillaume
 */
abstract class HttpBuffer {

    private function __construct() {
        
    }

    private function __clone() {
        
    }

    /**
     * 
     */
    public static function start() {
        ignore_user_abort(true); //avoid apache to kill the php running
        ob_start(); //start buffer output
    }

    /**
     * 
     */
    public static function end() {
        header("Content-Encoding: none"); //send header to avoid the browser side to take content as gzip format
        header("Content-Length: " . ob_get_length()); //send length header
        header("Connection: close"); //or redirect to some url: header('Location: http://www.google.com');
        ob_end_flush();
        flush(); //really send content, can't change the order:1.ob buffer to normal buffer, 2.normal buffer to output
        fastcgi_finish_request();
    }

}
