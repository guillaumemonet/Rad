<?php

namespace Rad\Http;

use Rad\Encryption\Encryption;
use Rad\Utils\Mime;
use Rad\Utils\StringUtils;

/*
 * Copyright (C) 2016 Guillaume Monet
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

/**
 * 
 */
final class Response {

    private $datas = null;
    private $time = null;
    private $type = null;
    private $secret = null;

    public function __construct() {
        $this->time = time();
    }

    public function setDataType($type) {
        $this->type = $type;
    }

    public function setData($datas) {
        $this->datas = $datas;
    }

    public function setSecret($secret) {
        $this->secret = $secret;
    }

    public function send() {
        self::doResponse($this->type);
        self::addHeader("Application-Nonce", $this->time);
        switch ($this->type) {
            case "xml":
                $xml = StringUtils::toXML($this->datas, "response");
                if ($this->secret != null) {
                    self::addHeader("Signature", Encryption::sign($xml, $this->secret));
                }
                echo $xml;
                break;
            case "text":
            case "txt":
                $txt = serialize($this->datas);
                if ($this->secret != null) {
                    self::addHeader("Signature", Encryption::sign($txt, $this->secret));
                }
                echo $txt;
                break;
            case "json":
                $json = json_encode($this->datas);
                if ($this->secret != null) {
                    self::addHeader("Signature", Encryption::sign($json, $this->secret));
                }
                echo $json;
                break;
            case "gif":
            case "jpeg":
            case "jpg":
            case "pdf":
                if ($this->secret != null) {
                    self::addHeader("Signature", Encryption::sign(base64_encode($this->datas), $this->secret));
                }
                echo $this->datas;
                break;
            default:
                echo $this->datas;
        }
    }

    /**
     * 
     * @param string $type
     * @param string $allow_origin
     * @param string $vary
     */
    public static function doResponse($type = "json", $allow_origin = "*", $vary = "User-Agent", $encoding = "utf-8") {
        if ($allow_origin !== null) {
            header('Access-Control-Allow-Origin: ' . $allow_origin);
        }
        if (isset(Mime::MIME_TYPES[$type]) && Mime::MIME_TYPES[$type][0] !== null) {
            header('Content-Type: ' . Mime::MIME_TYPES[$type][0] . "; charset=" . $encoding);
        } else {
            header('Content-Type: ' . Mime::MIME_TYPES["json"][0] . "; charset=" . $encoding);
        }
        if ($vary !== null) {
            header('Vary: ' . $vary);
        }
    }

    public static function addHeader($type, $content) {
        header($type . ": " . $content);
    }

    /**
     * @param int  $statusCode
     * @param type $redirect_url
     */
    public static function headerStatus($statusCode, $redirect_url = null) {
        if (isset(self::$status_codes[$statusCode]) && self::$status_codes[$statusCode] !== null) {
            $status_string = $statusCode . ' ' . self::$status_codes[$statusCode];
            header($_SERVER['SERVER_PROTOCOL'] . ' ' . $status_string, true, $statusCode);
            if ($redirect_url !== null && StringUtils::isURL($redirect_url)) {
                header('Location: ' . $redirect_url);
                exit;
            }
        }
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
    }

}
