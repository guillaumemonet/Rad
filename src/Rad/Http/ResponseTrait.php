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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Description of ResponseTrait
 *
 * @author guillaume
 */
trait ResponseTrait {

    //put your code here
    public function getBody(): StreamInterface {
        
    }

    public function getHeader($name): array {
        
    }

    public function getHeaderLine($name): string {
        
    }

    public function getHeaders(): array {
        
    }

    public function getProtocolVersion(): string {
        
    }

    public function getReasonPhrase(): string {
        
    }

    public function getStatusCode(): int {
        
    }

    public function hasHeader($name): bool {
        
    }

    public function withAddedHeader($name, $value): self {
        
    }

    public function withBody(StreamInterface $body): self {
        
    }

    public function withHeader($name, $value): self {
        
    }

    public function withProtocolVersion($version): self {
        
    }

    public function withStatus($code, $reasonPhrase = ''): self {
        
    }

    public function withoutHeader($name): self {
        
    }

}
