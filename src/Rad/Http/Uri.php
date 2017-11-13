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
 * Description of Uri
 *
 * @author guillaume
 */
use ParseError;
use Psr\Http\Message\UriInterface;

class Uri implements UriInterface {

    use UriTrait;

    const defaultHost = 'localhost';

    private $scheme = null;
    private $host = null;
    private $port = null;
    private $user = null;
    private $password = null;
    private $path = null;
    private $query = null;
    private $fragment = null;

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
            throw new ParseError("Url : unable to parse url \"{$url}\"");
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

    /**
     * 
     * @return bool
     */
    public function isSecure(): bool {
        return in_array($this->scheme, ['https', 'sftp']);
    }

    /**
     * 
     * @return Uri
     */
    public static function getCurrentUrl(): Uri {
        $url = new Uri('http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}:{$_SERVER['SERVER_PORT']}{$_SERVER['REQUEST_URI']}");
        return $url;
    }

    /**
     * Valid if current provided string is an URL
     * @param string $url
     * @return bool
     */
    public static function isURL(string $url): bool {
        return (boolean) !(filter_var($url, FILTER_SANITIZE_URL | FILTER_VALIDATE_URL) === false);
    }

}
