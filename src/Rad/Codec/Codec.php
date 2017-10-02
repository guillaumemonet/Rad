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

namespace Rad\Codec;

use Rad\Codec\CodecInterface;
use Rad\Codec\DefaultCodec;
use Rad\Codec\JsonCodec;
use Rad\Errors\CodecException;

/**
 * Description of Codec
 *
 * @author guillaume
 */
class Codec {

    private $codecs = array();

    public function __construct() {
        $this->add(new JsonCodec());
        $this->add(new DefaultCodec());
    }

    /**
     * 
     * @param CodecInterface $codec
     * @return $this
     */
    public function add(CodecInterface $codec) {
        foreach ($codec->getMimeTypes() as $mime) {
            $this->codecs[$mime] = &$codec;
        }
        return $this;
    }

    /**
     * 
     * @param type $datas
     * @param string $mime_type
     * @return type
     * @throws CodecException
     */
    public function serialize($datas, string $mime_type = "*") {
        if (isset($this->codecs[$mime_type])) {
            return $this->codecs[$mime_type]->serialize($datas);
        } else {
            throw new CodecException("No Codec found for serializing " . $mime_type);
        }
    }

    /**
     * 
     * @param type $datas
     * @param string $mime_type
     * @return type
     * @throws CodecException
     */
    public function deserialize($datas, string $mime_type = "*") {
        if (isset($this->codecs[$mime_type])) {
            return $this->codecs[$mime_type]->deserialize($datas);
        } else {
            throw new CodecException("No Codec found for deserializing " . $mime_type);
        }
    }

    /**
     * 
     * @return string
     */
    public function __toString(): string {
        $ret = "";
        foreach ($this->codecs as $codec) {
            $ret .= "" . $codec;
        }
        return $ret;
    }

}
