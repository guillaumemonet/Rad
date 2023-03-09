<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Http;

/**
 * Description of RequestTrait
 *
 * @author guillaume
 */
trait RequestTrait {

    public function getRequestURI() {
        return $_SERVER["REQUEST_URI"];
    }

    public function isCache() {
        return ($this->getHeader('Cache-Control') !== null) ? (!current($this->getHeader('Cache-Control')) == 'no-cache') : true;
    }

    /**
     * Check if current connection is secure.
     *
     * @return bool
     */
    public function isSecure(): bool {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    }

    /**
     * Return the current server name
     * @return string
     */
    public function getHost(): string {
        return $_SERVER['SERVER_NAME'];
    }

    /**
     * 
     * @return bool
     */
    public function isXhr(): bool {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * If your visitor comes from proxy server you have use another function
     * to get a real IP address:
     * @return string or false if no ip get
     */
    public function getRealIPAddress() {
        $ip = null;
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return filter_var($ip, FILTER_VALIDATE_IP);
    }

    public static function getAllHeaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

}
