<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Http;

use Rad\Log\Log;

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
    public static function doRequest($url, array $get_array = null, array $post_array = null, array $headers_array = null) {
        $post_params            = null;
        $get_params             = null;
        $opts                   = [];
        $opts['http']['method'] = 'GET';
        if ($headers_array != null) {
            $opts['http']['header'] = implode(',', $headers_array);
        }

        if ($get_array !== null) {
            $get_params = array_filter($get_array, function ($value) {
                return $value !== null;
            });
            $url .= (str_contains('?', $url) ? '&' : '?') . http_build_query($get_params);
        }

        if ($post_array !== null && count($post_params) > 0) {
            $post_params = array_filter($post_array, function ($value) {
                return $value !== null;
            });
            $opts['http']['method']  = 'POST';
            $opts['http']['header']  .= 'Content-type: application/x-www-form-urlencoded';
            $opts['http']['content'] = http_build_query($post_params);
        }
        $context = stream_context_create($opts);
        Log::getHandler()->debug($url);
        return file_get_contents($url, false, $context);
    }

}
