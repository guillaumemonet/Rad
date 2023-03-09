<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Http;

/**
 * Simple Http Query
 *
 * @author guillaume
 */
abstract class HttpClient {

    private function __construct() {
        
    }

    private function __clone() {
        
    }

    /**
     * 
     * @param string $url
     * @param array $get_array
     * @param array $post_array
     * @return type
     */
    public static function doRequest($url, array $get_array = null, array $post_array = null) {
        $context     = null;
        $post_params = null;
        $get_params  = null;

        if ($get_array !== null) {
            $get_params = array_filter($get_array, function ($value) {
                return $value !== null;
            });
        }

        if ($post_array !== null && count($post_params) > 0) {
            $post_params = array_filter($post_array, function ($value) {
                return $value !== null;
            });
            $opts    = ['http' =>
                [
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => http_build_query($post_params)
                ]
            ];
            $context = stream_context_create($opts);
        }
        $url .= "?" . http_build_query($get_params);
        return file_get_contents($url, false, $context);
    }

}
