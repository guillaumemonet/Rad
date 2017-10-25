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

namespace Rad\bin\scripts;

/**
 * Description of Index
 *
 * @author guillaume
 */
class Index {

    public $name;
    public $columns;
    public $unique = 0;

    public function getPrimaryColumns($type) {
        $ret = array();
        foreach ($this->columns as $col_name => $col) {
            if ($col->key == "PRI") {
                $ret[] = $col->getAsVar($type);
            }
        }
        return $ret;
    }

    public function getNotPrimaryColumns($type, $ignoreSpecial = false) {
        $ret = array();
        foreach ($this->columns as $col_name => $col) {
            if ($col->key !== "PRI") {
                if (!$ignoreSpecial || (!in_array($col_name, array("password", "token")))) {
                    $ret[] = $col->getAsVar($type);
                }
            }
        }
        return $ret;
    }

    public function getNotAutoIncColumns($type) {
        $ret = array();
        foreach ($this->columns as $col_name => $col) {
            if ($col->auto == 0) {
                $ret[] = $col->getAsVar($type);
            }
        }
        return $ret;
    }

    public function getColumns($type, $ignoreSpecial = false) {
        $ret = array();
        foreach ($this->columns as $col_name => $col) {
            if (!$ignoreSpecial || (!in_array($col_name, array("password", "token")))) {
                $ret[] = $col->getAsVar($type);
            }
        }
        return $ret;
    }

}
