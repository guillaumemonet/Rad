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

namespace Rad\Utils;

/**
 * Description of Filter
 *
 * @author guillaume
 */
final class Filter {

    private function __construct() {
        
    }

    /**
     * Return mathing objects with get filter
     * @param array $datas
     * @param array $get
     */
    public static function matchFilter(array &$datas, array $get) {
        if (sizeof($get) > 0) {
            $datas = array_filter($datas, function($obj) use($get) {
                return array_intersect_assoc((array) $obj, $get) == $get;
            });
        }
    }

    /**
     * 
     * @param array $datas
     * @param array $get
     */
    public static function containsFilter(array &$datas, array $get) {
        if (count($get) > 0) {
            $datas = array_filter($datas, function($obj) use($get) {
                return count(array_uintersect_assoc((array) $obj, $get, function($a, $b) {
                                    return !stristr($a, $b);
                                })) > 0;
            });
        }
    }

}
