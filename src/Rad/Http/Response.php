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

use GuzzleHttp\Psr7\Response as GResponse;
use Psr\Http\Message\StreamInterface;
use Rad\Codec\Codec;

/**
 * 
 */
class Response extends GResponse {

    private $type   = null;
    private $secret = null;

    public function __construct(int $statusCode = 200, array $headers = [], StreamInterface $body = null) {
        $baseHeaders  = [
            "Application-Nonce" => [time()],
            'X-Powered-By'      => ['Rad Framework']
        ];
        $mergedHeader = array_merge($headers, $baseHeaders);
        parent::__construct($statusCode, $mergedHeader, $body);
    }

    public function setDataType($type) {
        $this->type = $type;
    }

    public function setSecret($secret) {
        $this->secret = $secret;
    }

    /**
     * 
     */
    public function send() {
        http_response_code($this->getStatusCode());
        $datas = Codec::getHandler($this->type)->serialize($this->getBody());
        if ($this->secret != null) {
            header('Signature: ' . Codec::getHandler($this->type)->sign($datas, $this->secret));
        }
        $headers = $this->getHeaders();
        array_walk($headers, function ($items, $key) {
            header($key . ': ' . implode(', ', $items));
        });
        echo($datas);
    }

    /**
     * @param int  $statusCode
     * @param type $redirect_url
     */
    public static function headerStatus($statusCode, $redirect_url = null) {
        if (StatusCode::httpHeaderFor($statusCode) !== null) {
            header(StatusCode::httpHeaderFor($statusCode));
            if ($redirect_url !== null && Uri::isURL($redirect_url)) {
                header('Location: ' . $redirect_url);
                exit;
            }
        }
    }

}
