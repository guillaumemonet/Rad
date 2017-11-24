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
use Rad\Error\Http\RequestedRangeException;

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
    private $_datas = null;
    private $cache = false;
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

        $this->body = $body ? $body : new Body(fopen('php://input', 'r+'));
        $this->setHeaders($headers);
        $this->uri = $uri;
        $this->method = strtoupper($method);
        if (!in_array($this->method, $this->allowed_method)) {
            throw new MethodNotAllowedException();
        }
        $this->authority = $this->getHeader("HTTP_AUTHORITY");
        $this->signature = $this->getHeader("HTTP_SIGNATURE");
        $this->content_type = $this->getHeader("CONTENT_TYPE");
        $this->accept_type = $this->getHeader("HTTP_ACCEPT_TYPE");
        $this->appname = $this->getHeader("HTTP_APPNAME");
        $this->context = $this->getHeader("HTTP_CONTEXT");
        $this->cache = !($this->getHeader("HTTP_CACHE_CONTROL") == "no-cache");
        $range = $this->getHeader("HTTP_RANGE");
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
        if (isset($_SERVER["REQUEST_URI"])) {
            $path = explode("/", trim(Uri::getCurrentUrl()->getPath(), "/"));
            $this->version = (int) str_replace("v", "", array_shift($path));
            $this->path = trim(filter_var(implode("/", $path), FILTER_SANITIZE_STRING), "/");
        }
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
