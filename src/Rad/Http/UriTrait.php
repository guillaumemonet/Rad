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
 * Description of UriTrait
 *
 * @author guillaume
 */
trait UriTrait {

    public function getAuthority(): string {
        return $this->host . ':' . $this->port;
    }

    public function getFragment(): string {
        return $this->fragment;
    }

    public function getHost(): string {
        return $this->host;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function getPort() {
        return $this->port;
    }

    public function getQuery(): string {
        return $this->query;
    }

    public function getQueryArray() {
        return null !== $this->query ? parse_str($this->query) : null;
    }

    public function getScheme(): string {
        return $this->scheme;
    }

    public function getUserInfo(): string {
        return $this->user . ':' . $this->password;
    }

    public function withFragment($fragment): self {
        $uri = clone $this;
        $uri->fragment = $fragment;
        return $uri;
    }

    public function withHost($host): self {
        $uri = clone $this;
        $uri->host = $host;
        return $uri;
    }

    public function withPath($path): self {
        $uri = clone $this;
        $uri->path = $path;
        return $uri;
    }

    public function withPort($port): self {
        $uri = clone $this;
        $uri->port = $port;
        return $uri;
    }

    public function withQuery($query): self {
        $uri = clone $this;
        $uri->query = $query;
        return $uri;
    }

    public function withScheme($scheme): self {
        $uri = clone $this;
        $uri->scheme = $scheme;
        return $uri;
    }

    public function withUserInfo($user, $password = null): self {
        $uri = clone $this;
        $uri->user = $user;
        $uri->password = $password;
        return $uri;
    }

}
