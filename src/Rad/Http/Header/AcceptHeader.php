<?php

/*
 * The MIT License
 *
 * Copyright 2018 guillaume.
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

namespace Rad\Http\Header;

/**
 * Description of AcceptHeader
 *
 * @author guillaume
 */
class AcceptHeader {

    public static function parse($acceptDatas) {
        $mimeTypes = [];
        $items = explode(',', $acceptDatas);
        array_map(function($item) use (&$mimeTypes) {
            $elements = explode(';', $item);
            $mime = current($elements);
            $mimeTypes[trim($mime)] = count($elements) === 1 ? 1.0 : str_replace('q=', '', $elements[1]);
        }, $items);
        arsort($mimeTypes);
        return $mimeTypes;
    }

}

//error_log(print_r(AcceptHeader::_parse("application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5"), true));

