<?php

/*
 * The MIT License
 *
 * Copyright 2017 guillaume.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Rad\Http;

/**
 * Description of RequestTrait
 *
 * @author guillaume
 */
trait RequestTrait {

    public function enabledCors() {
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) && (
                    $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'POST' ||
                    $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'DELETE' ||
                    $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'PUT' ||
                    $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'GET' )) {
                header('Access-Control-Allow-Origin: *');
                header("Access-Control-Allow-Credentials: true");
                header('Access-Control-Allow-Headers: X-Requested-With');
                header('Access-Control-Allow-Headers: Content-Type');
                header('Access-Control-Allow-Headers: Accept-Type');
                header('Access-Control-Allow-Headers: Range');
                header('Access-Control-Allow-Headers: Content-Range');
                header('Access-Control-Allow-Headers: Appname');
                header('Access-Control-Allow-Headers: Context');
                header('Access-Control-Allow-Headers: Signature');
                header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT'); // http://stackoverflow.com/a/7605119/578667
                header('Access-Control-Max-Age: 86400');
            }
            exit;
        }
    }

    public function getRequestURI() {
        return $_SERVER["REQUEST_URI"];
    }

    /**
     * 
     */
    protected function checkMethod(): bool {
        
    }

    public function isCache() {
        return ($this->getHeader('Cache-Control') !== null) ? (!current($this->getHeader('Cache-Control')) == 'no-cache') : true;
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
    public function isSecure(): bool {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    }

    /**
     * Return if requested by a bot
     */
    public function isBot(): bool {
        return Bot::isBot();
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
