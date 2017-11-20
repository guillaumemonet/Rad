<?php

/*
 * The MIT License
 *
 * Copyright 2017 Guillaume Monet.
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

use Psr\Http\Message\ResponseInterface;
use Rad\Codec\Codec;
use Rad\Utils\Mime;
use Rad\Utils\StringUtils;

/**
 * 
 */
class Response implements ResponseInterface {

    use MessageTrait;
    use ResponseTrait;

    protected $code = 200;
    protected $reasonPhrase = '';
    private $datas = null;
    private $time = null;
    private $type = null;
    private $secret = null;

    public function __construct($code = 200, $reasonPhrase = '', $headers = array()) {
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
        $datas = Codec::getHandler($this->type)->serialize($this->datas);
        if ($this->secret != null) {
            self::addHeader("Signature", Codec::getHandler($this->type)->sign($datas, $this->secret));
        }
        echo $datas;
    }

    /**
     * 
     * @param string $type
     * @param string $allow_origin
     * @param string $vary
     */
    public function doResponse($type = "json", $allow_origin = "*", $vary = "User-Agent", $encoding = "utf-8") {
        if ($allow_origin !== null) {
            $this->addHeader('Access-Control-Allow-Origin', $allow_origin);
        }
        $this->addHeader("Access-Control-Expose-Headers", "Content-Range");
        if (isset(Mime::MIME_TYPES[$type]) && Mime::MIME_TYPES[$type][0] !== null) {
            $this->addHeader('Content-Type', Mime::MIME_TYPES[$type][0] . '; charset=' . $encoding);
        } else {
            $this->addHeader('Content-Type', Mime::MIME_TYPES["json"][0] . '; charset=' . $encoding);
        }
        if ($vary !== null) {
            $this->addHeader('Vary', $vary);
        }
        $this->addHeader('X-Powered-By', 'Rad Framework');
    }

    public static function addHeader($type, $content) {
        header($type . ": " . $content);
    }

    /**
     * @param int  $statusCode
     * @param type $redirect_url
     */
    public static function headerStatus($statusCode, $redirect_url = null) {
        if (StatusCode::httpHeaderFor($statusCode) !== null) {
            header(StatusCode::httpHeaderFor($statusCode));
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
