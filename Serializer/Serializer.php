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

namespace Rad\Serializer;

use ErrorException;

/**
 * Description of Serializer
 *
 * @author Guillaume Monet
 */
class Serializer {

    private $serializers = array();

    public function __construct() {
        
    }

    public function addSerializer(array $mime_types, ISerializer $serializer) {
        foreach ($mime_types as $mime) {
            $this->parsers[$mime] = $serializer;
        }
    }

    public function serialize($mime_type, $datas) {
        if (isset($this->serializers[$mime_type])) {
            return call_user_func_array([$this->serializers[$mime_type], 'serialize'], [$datas]);
        } else {
            throw new ErrorException("No parser found for " . $mime_type);
        }
    }

}
