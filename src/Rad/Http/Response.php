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
use Psr\Http\Message\StreamInterface;

/**
 * 
 */
class Response implements ResponseInterface {

    use MessageTrait;
    use ResponseTrait;

    protected $statusCode = 200;
    protected $reasonPhrase = '';
    protected $time = null;

    public function __construct($statusCode = 200, string $reasonPhrase = '', $headers = [], StreamInterface $body = null) {
        $this->time = time();
        $this->statusCode = $statusCode;
        if ($reasonPhrase === '' && null !== StatusCode::getMessageForCode($this->statusCode)) {
            $this->reasonPhrase = StatusCode::getMessageForCode($this->statusCode);
        } else {
            $this->reasonPhrase = (string) $reasonPhrase;
        }

        $this->body = $body ? $body : new Body(fopen('php://temp', 'r+'));
        $this->setHeaders($headers);
        $this->addHeader("Application-Nonce", $this->time);
        $this->addHeader('X-Powered-By', 'Rad Framework');
    }

    public function getReasonPhrase(): string {
        return $this->reasonPhrase;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function withStatus($code, $reasonPhrase = ''): self {
        $ret = clone $this;
        $ret->code = $code;
        if ($reasonPhrase == '' && null !== StatusCode::getMessageForCode($code)) {
            $ret->reasonPhrase = StatusCode::getMessageForCode($code);
        } else {
            $ret->reasonPhrase = (string) $reasonPhrase;
        }
        return $ret;
    }

}
