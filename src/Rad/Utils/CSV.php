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

namespace Rad\Utils;

/**
 * Description of CSVReader
 *
 * @author Guillaume Monet
 */
class CSV {

    private $filename = null;
    private $hasHeader = null;
    private $separator = ";";

    public function __construct($filename, $separator = ";", $hasHeader = false) {
        $this->filename = $filename;
        $this->hasHeader = $hasHeader;
        $this->separator = $separator;
    }

    /**
     * 
     * @return array
     */
    public function read(): array {
        $all_rows = [];
        $header = null;
        $file = fopen($this->filename, 'r+');
        while ($row = fgetcsv($file)) {
            if ($header === null) {
                $header = $row;
                continue;
            }
            $all_rows[] = array_combine($header, $row);
        }
        fclose($file);
        return $all_rows;
    }

}
