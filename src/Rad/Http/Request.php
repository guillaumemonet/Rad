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

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Rad\Error\Http\MethodNotAllowedException;

/**
 * Description of Request.
 *
 * @author Admin
 */
class Request implements RequestInterface {

    use MessageTrait;
    use RequestTrait;

    /**
     *
     * @var UriInterface
     */
    protected $uri = null;

    /**
     *
     * @var string
     */
    protected $method = null;

    /**
     *
     * @var string
     */
    protected $requestTarget;
    private $allowed_method = array("POST", "GET", "PATCH", "PUT", "OPTIONS");
    public $path_datas = [];
    public $get_datas = [];
    public $post_datas = [];
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
    public $path = null;

    public function __construct(string $method = 'GET', array $headers = [], UriInterface $uri = null, StreamInterface $body = null) {
        $this->method = strtoupper($method);
        if (!in_array($this->method, $this->allowed_method)) {
            throw new MethodNotAllowedException();
        }
        $this->body = $body ? $body : new Body(fopen('php://input', 'r+'));
        $this->setHeaders($headers);
        $this->uri = $uri;
    }

    public function getRequestTarget(): string {
        
    }

    public function getUri(): UriInterface {
        return $this->uri;
    }

    public function withMethod($method): self {
        $ret = clone $this;
        $ret->method = $method;
        return $ret;
    }

    public function withRequestTarget($requestTarget): self {
        
    }

    public function withUri(UriInterface $uri, $preserveHost = false): self {
        $ret = clone $this;
        $uri = $this->getUri()->getAuthority() . "";
        return $ret;
    }

}
