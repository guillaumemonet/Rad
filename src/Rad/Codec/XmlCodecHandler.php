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
use Rad\Utils\Mime;

/**
 * Description of Xml_CodecHandler
 *
 * @author guillaume
 */
class XmlCodecHandler implements CodecInterface {

    public function __toString() {
        return "XML encode/decode (to array)";
    }

    public function deserialize(string $string) {
        throw new ErrorException("Not supported yet!");
    }

    public function getMimeTypes(): array {
        return ['xml'];
    }

    public function serialize($object): string {
        $backup = libxml_disable_entity_loader(true);
        $backup_errors = libxml_use_internal_errors(true);
        $result = simplexml_load_string($object);
        libxml_disable_entity_loader($backup);
        libxml_clear_errors();
        libxml_use_internal_errors($backup_errors);
        if ($result === false) {
            return null;
        }
        return $result;
    }

    public function sign($datas, $secret) {
        
    }

}
