<?php

/*
 * Copyright (C) 2016 Admin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Rad\Http;

use Rad\Http\Error\MethodNotAllowedException;
use Rad\Http\Error\RequestedRangeException;

/**
 * Description of Request.
 *
 * @author Admin
 */
final class Request {

    /**
     *
     * @var array
     */
    private $allowed_method = array("POST", "GET", "PATCH", "PUT", "OPTIONS");
    private $_datas = null;
    private $cache = false;
    public $method = null;
    public $path_datas = array();
    public $get_datas = array();
    public $post_datas = array();
    public $signature = null;
    public $authority = null;
    public $content_type = null;
    public $accept_type;
    public $user = null;
    public $user_name = null;
    public $user_token = null;
    public $app = null;
    public $appname = null;
    public $customer = null;
    public $limit = null;
    public $offset = null;
    public $version = null;
    public $path = null;

    public function __construct() {
        //Log::debug(print_r($_SERVER,true));
        $this->method = strtoupper(self::getHeader("REQUEST_METHOD"));
        if (!in_array($this->method, $this->allowed_method)) {
            throw new MethodNotAllowedException();
        }
        $this->authority = self::getHeader("HTTP_AUTHORITY");
        $this->signature = self::getHeader("HTTP_SIGNATURE");
        $this->content_type = self::getHeader("CONTENT_TYPE");
        $this->accept_type = self::getHeader("HTTP_ACCEPT_TYPE");
        $this->appname = self::getHeader("HTTP_APPNAME");
        $this->context = self::getHeader("HTTP_CONTEXT");
        $this->cache = self::getHeader("HTTP_CACHE_CONTROL") == "cache" ? true : false;
        $this->cache = true;
        $range = self::getHeader("HTTP_RANGE");
        if ($range != null && strlen($range) > 0) {
            $limits = explode("-", $range);
            if (count($limits) > 2 || count($limits) == 0) {
                throw new RequestedRangeException();
            } else {
                $this->limit = (int) $limits[0];
                $this->offset = (int) $limits[1];
            }
        }
        $array_authority = explode(":", $this->authority);
        if (sizeof($array_authority) == 2) {
            $this->user_name = $array_authority[0];
            $this->user_token = $array_authority[1];
        }

        $this->path = trim(filter_var($_GET["api_path"], FILTER_SANITIZE_STRING), "/");
        if ($this->method == "POST") {
            $this->_datas = file_get_contents("php://input");
        }
        $this->version = (int) trim(filter_var($_GET["api_version"], FILTER_SANITIZE_STRING), "/");

        $post = filter_input_array(INPUT_POST);
        if ($post !== null && is_array($post)) {
            foreach ($post as $key => $value) {
                $this->post_datas[$key] = $value;
            }
        }
        $_GET = array_diff_key($_GET, array("api_path" => "", "api_version" => ""));
        $get = filter_input_array(INPUT_GET);
        if ($get !== null && is_array($get)) {
            foreach ($get as $key => $value) {
                if ($key != "api_path" && $key != "api_version") {
                    $this->get_datas[$key] = $value;
                }
            }
        }
    }

    public function getDatas() {
        return $this->_datas;
    }

    public function isCache() {
        return $this->cache;
    }

    public function getMethod() {
        return $this->method;
    }

    public function getPath() {
        return $this->path;
    }

    /**
     * Check if current connection is secure.
     *
     * @return bool
     */
    public static function isSecure() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    }

    /**
     * Return if requested by a bot
     */
    public static function isBot() {
        return Bot::isBot();
    }

    /**
     * Return the current server name
     * @return string
     */
    public static function getHost() {
        return $_SERVER['SERVER_NAME'];
    }

    /**
     * 
     * @return strings
     */
    public static function getDomain() {
        $h = $_SERVER['SERVER_NAME'];
        $a = explode(".", $h);
        if (count($a) > 1) {
            return $a[count($a) - 2] . "." . $a[count($a) - 1];
        } else {
            return null;
        }
    }

    /**
     * If your visitor comes from proxy server you have use another function
     * to get a real IP address:
     * @return string or false if no ip get
     */
    public static function getRealIPAddress() {
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

    /**
     * 
     * @param string $header
     * @return string
     */
    public static function getHeader($header) {
        /* if (self::$headers == null) {
          self::$headers = apache_request_headers();
          self::$headers = array_merge(self::$headers, $_SERVER);
          } */
        //return self::$headers[$header];
        if (isset($_SERVER[$header])) {
            return $_SERVER[$header];
        } else {
            return null;
        }
    }

    /**
     * 
     * @return array
     */
    public static function getAllHeaders() {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

}
