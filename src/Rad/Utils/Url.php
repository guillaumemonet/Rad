<?php

namespace Rad\Utils;

class Url {

    protected $original_url = null;
    protected $scheme = null;
    protected $host = null;
    protected $port = null;
    protected $user = null;
    protected $password = null;
    protected $path = null;
    protected $query = null;
    protected $fragment = null;

    public function __construct($url = null) {
        if ($url) {
            $this->parseUrl($url);
        }
    }

    /**
     * 
     * @param string $url
     * @throws ParseError
     */
    public function parseUrl(string $url) {

        $this->original_url = $url;

        if (false === ($tokens = parse_url($url))) {
            throw new ParseError("Url : unable to parse url \"{$url}\"");
        }

        $this->scheme = $tokens['scheme'];
        $this->host = $tokens['host'];
        $this->port = (int) $tokens['port'];
        $this->user = $tokens['user'];
        $this->password = $tokens['password'];
        $this->path = $tokens['path'];
        $this->query = $tokens['query'];
        $this->fragment = $tokens['fragment'];

        if (null !== $this->query) {
            parse_str($this->query, $this->query);
        }
    }

    /**
     * @return string
     */
    public function __toString(): string {

        $url = ($this->path ? $this->path : '')
                . ($this->query ? '?' . http_build_query($this->query) : '')
                . ($this->fragment ? '#' . $this->fragment : '');

        if ($this->host && in_array(substr($url, 0, 1), ['/', '?', '#'])) {
            $url = ($this->scheme ? $this->scheme . '://' : '//')
                    . ($this->user ? $this->user . ($this->password ? ':' . $this->password : '') . '@' : '')
                    . $this->host
                    . ($this->port ? ':' . $this->port : '')
                    . $url;
        }

        return $url;
    }

    /**
     * 
     * @return string
     */
    public function getOriginalUrl(): string {
        return $this->original_url;
    }

    /**
     * 
     * @param string $fragment
     * @return \Url
     */
    public function setFragment(string $fragment): Url {
        $this->fragment = $fragment;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getFragment(): string {
        return $this->fragment;
    }

    /**
     * 
     * @param string $path
     * @return \Url
     */
    public function setPath(string $path): Url {
        $this->path = $path;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getPath(): string {
        return $this->path;
    }

    /**
     * 
     * @param string $password
     * @return \Url
     */
    public function setPassword(string $password): Url {
        $this->password = $password;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getPassword(): string {
        return $this->password;
    }

    /**
     * 
     * @param string $user
     * @return \Url
     */
    public function setUser(string $user): Url {
        $this->user = $user;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getUser(): string {
        return $this->user;
    }

    /**
     * 
     * @param int $port
     * @return \Url
     */
    public function setPort(int $port): Url {
        $this->port = $port;
        return $this;
    }

    /**
     * 
     * @return int
     */
    public function getPort(): int {
        return $this->port;
    }

    /**
     * 
     * @param string $scheme
     * @return \Url
     */
    public function setScheme(string $scheme): Url {
        $this->scheme = $scheme;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getScheme(): string {
        return $this->scheme;
    }

    /**
     * 
     * @param string $host
     * @return \Url
     */
    public function setHost(string $host): Url {
        $this->host = $host;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getHost(): string {
        return $this->host;
    }

    /**
     * 
     * @param array $query
     * @return \Url
     */
    public function setQuery(array $query): Url {
        $this->query = $query;
        return $this;
    }

    /**
     * 
     * @return mixed
     */
    public function &getQuery() {
        return $this->query;
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
     * @return Url
     */
    public static function getCurrentUrl(): Url {
        $url = new Url();
        $url->parseUrl('http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
        if (!$_SERVER['HTTPS'] && $_SERVER['SERVER_PORT'] != 80 || $_SERVER['HTTPS'] && $_SERVER['SERVER_PORT'] != 443) {
            $url->setPort($_SERVER['SERVER_PORT']);
        }
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
