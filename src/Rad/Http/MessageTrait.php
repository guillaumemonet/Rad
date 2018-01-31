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
 * Description of MessageTrait
 *
 * @author guillaume
 */
use Psr\Http\Message\StreamInterface;

/**
 * 
 */
trait MessageTrait {

    protected $headers = [];
    protected $version = '1.1';

    /**
     *
     * @var StreamInterface
     */
    protected $body = null;

    public function getBody(): StreamInterface {
        return $this->body;
    }

    public function getHeader($name) {
        return isset($this->headers[$name]) ? $this->headers[$name] : null;
    }

    public function getHeaderLine($name): string {
        $headers = $this->getHeader($name);
        return $headers !== null ? implode(",", $headers) : null;
    }

    public function getHeaders(): array {
        return $this->headers;
    }

    public function getProtocolVersion(): string {
        return $this->version;
    }

    public function hasHeader($name): bool {
        return $this->getHeader($name) !== null;
    }

    public function withAddedHeader($name, $value): self {
        $ret = clone $this;
        $ret->addHeader($name, $value);
        return $ret;
    }

    public function withBody(StreamInterface $body): self {
        $ret = clone $this;
        $ret->body = $body;
        return $ret;
    }

    public function withHeader($name, $value): self {
        $ret = clone $this;
        $ret->setHeader($name, $value);
        return $ret;
    }

    public function withProtocolVersion($version): self {
        $ret = clone $this;
        $ret->version = $version;
        return $ret;
    }

    public function withoutHeader($name): self {
        $ret = clone $this;
        unset($ret->headers[strtolower($name)]);
        return $ret;
    }

    protected function addHeader($name, $value) {
        if (!$this->hasHeader($name)) {
            $this->headers[$name] = [];
        }
        $this->headers[$name][] = $value;
    }

    protected function setHeader($name, $value) {
        if (!$this->hasHeader($name)) {
            $this->headers[$name] = [];
        }
        $this->headers[$name] = [$value];
    }

    protected function setHeaders(array $headers) {
        array_walk($headers, function($value, $key) {
            $this->addHeader($key, $value);
        });
    }

    protected function addHeaders(array $headers) {
        array_walk($headers, function($value, $key) {
            $this->addHeader($key, $value);
        });
    }

}
