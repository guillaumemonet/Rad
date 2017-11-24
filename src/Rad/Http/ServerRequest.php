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

use Psr\Http\Message\ServerRequestInterface;

/**
 * Description of ServerRequest
 *
 * @author guillaume
 */
class ServerRequest extends Request implements ServerRequestInterface {

    use MessageTrait;
    use RequestTrait;
    use ServerRequestTrait;

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var array
     */
    private $cookieParams = [];

    /**
     * @var null|array|object
     */
    private $parsedBody;

    /**
     * @var array
     */
    private $queryParams = [];

    /**
     * @var array
     */
    private $serverParams;

    /**
     * @var array
     */
    private $uploadedFiles = [];

    public function __construct() {
        parent::__construct($_SERVER['REQUEST_METHOD'], $_SERVER, Uri::getCurrentUrl());
    }

    /**
     * 
     */
    public function getServerParams() {
        return $this->serverParams;
    }

    /**
     * 
     */
    public function getUploadedFiles() {
        return $this->uploadedFiles;
    }

    /**
     * 
     */
    public function withUploadedFiles(array $uploadedFiles) {
        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;
        return $new;
    }

    /**
     * 
     */
    public function getCookieParams() {
        return $this->cookieParams;
    }

    /**
     * 
     */
    public function withCookieParams(array $cookies) {
        $new = clone $this;
        $new->cookieParams = $cookies;
        return $new;
    }

    /**
     * 
     */
    public function getQueryParams() {
        return $this->queryParams;
    }

    /**
     * 
     */
    public function withQueryParams(array $query) {
        $new = clone $this;
        $new->queryParams = $query;
        return $new;
    }

    /**
     * 
     */
    public function getParsedBody() {
        return $this->parsedBody;
    }

    /**
     * 
     */
    public function withParsedBody($data) {
        $new = clone $this;
        $new->parsedBody = $data;
        return $new;
    }

    /**
     * 
     */
    public function getAttributes() {
        return $this->attributes;
    }

    /**
     * 
     */
    public function getAttribute($attribute, $default = null) {
        if (false === array_key_exists($attribute, $this->attributes)) {
            return $default;
        }
        return $this->attributes[$attribute];
    }

    /**
     * 
     */
    public function withAttribute($attribute, $value) {
        $new = clone $this;
        $new->attributes[$attribute] = $value;
        return $new;
    }

    /**
     * 
     */
    public function withoutAttribute($attribute) {
        if (false === array_key_exists($attribute, $this->attributes)) {
            return $this;
        }
        $new = clone $this;
        unset($new->attributes[$attribute]);
        return $new;
    }

}
