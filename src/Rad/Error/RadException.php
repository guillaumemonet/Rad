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

namespace Rad\Error;

use ErrorException;
use JsonSerializable;

/**
 * Description of DefaultError
 *
 * @author guillaume
 */
abstract class RadException extends ErrorException implements JsonSerializable {

    /**
     * Constructor
     *
     * @param string $message Service exception
     * @param int $code Status code, defaults to 500
     */
    public function __construct(?string $message = null, int $code = 500) {
        if (empty($message)) {
            $message = 'RAD Exception';
        }
        parent::__construct($message, $code);
    }

    public function __toString(): string {
        return $this->message;
    }

    public function jsonSerialize(): mixed {
        return json_encode((array) $this);
    }

}
