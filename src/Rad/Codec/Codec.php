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

use ErrorException;
use Rad\Errors\CodecException;

/**
 * Description of Codec
 *
 * @author guillaume
 */
class Codec {

    private static $codecs = array();

    /**
     * 
     * @param array $mime_types
     * @param CodecInterface $codec
     * @return Codec
     */
    public static function add(CodecInterface $codec) {
        foreach ($codec->getMimeTypes() as $mime) {
            self::$codecs[$mime] = &$codec;
        }
        return self::class;
    }

    public static function init() {
        self::add(new JsonCodec());
        self::add(new DefaultCodec());
    }

    /**
     * 
     * @param type $mime_type
     * @param type $datas
     * @return type
     * @throws ErrorException
     */
    public static function serialize($datas, string $mime_type = "*") {
        if (isset(self::$codecs[$mime_type])) {
            return self::$codecs[$mime_type]->serialize($datas);
        } else {
            throw new CodecException("No Codec found for serializing " . $mime_type);
        }
    }

    /**
     * 
     * @param type $mime_type
     * @param type $datas
     * @return type
     * @throws ErrorException
     */
    public static function deserialize($datas, string $mime_type = "*") {
        if (isset(self::$codecs[$mime_type])) {
            return self::$codecs[$mime_type]->deserialize($datas);
        } else {
            throw new CodecException("No Codec found for deserializing " . $mime_type);
        }
    }

    /**
     * 
     * @return string
     */
    public static function listCodecs(): string {
        $ret = "";
        foreach (self::$codecs as $codec) {
            $ret .= "" . $codec;
        }
        return $ret;
    }

}
