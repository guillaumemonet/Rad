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
 * Description of Column
 *
 * @author guillaume
 */
class Column {

    public $name;
    public $type_sql;
    public $type_php;
    public $auto = 0;
    public $param;
    public $default;
    public $key;

    public function getAsVar($type) {
        switch ($type) {
            case "name":
                return $this->name;
            case "sql_name":
                return "`" . $this->name . "`";
            case "php":
                return '$' . $this->name;
            case "php_prefix":
                return ''.$this->type_php.' $' . $this->name;
            case "sql_cond":
                if ($this->name == "password") {
                    return "`" . $this->name . "` = PASSWORD(:" . $this->name . ")";
                } else {
                    return "`" . $this->name . "` = :" . $this->name;
                }
            case "sql_param":
                if ($this->name == "password") {
                    return "PASSWORD(:" . $this->name . ")";
                } else {
                    return ":" . $this->name;
                }

            case "bind":
                return '":' . $this->name . '"' . ' => $' . $this->name;

            case "bind_this":
                return '":' . $this->name . '"' . ' => $this->' . $this->name;

            case "this":
                return '$this->' . $this->name;
        }
    }

}
