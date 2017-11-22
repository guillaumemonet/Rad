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
 * PSR 7 Compilant UriInterface
 *
 * @author guillaume
 */
use ParseError;
use Psr\Http\Message\UriInterface;

class Uri implements UriInterface {

    use UriTrait;

    const defaultHost = 'localhost';

    protected $scheme = null;
    protected $host = null;
    protected $port = null;
    protected $user = null;
    protected $password = null;
    protected $path = null;
    protected $query = null;
    protected $fragment = null;

    public function __construct(string $uri = '') {
        $this->parseUrl($uri);
    }

    /**
     * 
     * @param string $url
     * @throws ParseError
     */
    private function parseUrl(string $url) {
        if (false === ($tokens = parse_url($url))) {
            throw new ParseError('Url : unable to parse url ' . $url);
        }
        $this->scheme = isset($tokens['scheme']) ? $tokens['scheme'] : null;
        $this->host = isset($tokens['host']) ? $tokens['host'] : null;
        $this->port = isset($tokens['port']) ? (int) $tokens['port'] : null;
        $this->user = isset($tokens['user']) ? $tokens['user'] : null;
        $this->password = isset($tokens['password']) ? $tokens['password'] : null;
        $this->path = isset($tokens['path']) ? $tokens['path'] : null;
        $this->query = isset($tokens['query']) ? $tokens['query'] : null;
        $this->fragment = isset($tokens['fragment']) ? $tokens['fragment'] : null;
    }

    public function __toString(): string {
        return ($this->scheme ? $this->scheme . '://' : '//')
                . ($this->user ? $this->user . ($this->password ? ':' . $this->password : '') . '@' : '')
                . ($this->host ? $this->host : self::defaultHost)
                . ($this->port ? ':' . $this->port : '')
                . ($this->path ? $this->path : '')
                . ($this->query ? '?' . http_build_query($this->query) : '')
                . ($this->fragment ? '#' . $this->fragment : '');
    }

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
