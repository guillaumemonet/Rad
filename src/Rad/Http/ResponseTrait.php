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

use Rad\Codec\Codec;
use Rad\Utils\Mime;
use Rad\Utils\StringUtils;

/**
 * Non psr 7 methods
 *
 * @author guillaume
 */
trait ResponseTrait {

    private $type = null;
    private $secret = null;

    public function setDataType($type) {
        $this->type = $type;
    }

    public function setSecret($secret) {
        $this->secret = $secret;
    }

    public function send() {
        //$this->doResponse();
        $datas = Codec::getHandler($this->type)->serialize($this->getBody());
        if ($this->secret != null) {
            $this->addHeader("Signature", Codec::getHandler($this->type)->sign($datas, $this->secret));
        }
        foreach ($this->getHeaders() as $key => $value) {
            header($key . ': ' . $this->getHeaderLine($key));
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

}
