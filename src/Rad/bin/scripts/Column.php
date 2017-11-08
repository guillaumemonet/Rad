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

    /**
     * 
     * @param string $type
     * @return string
     */
    public function getAsVar(string $type) {
        return $this->generateFormatTab()[$type];
    }

    private function generateFormatTab() {
        return array(
            "name" => $this->name,
            "sql_name" => "`" . $this->name . "`",
            "php" => '$' . $this->name,
            "php_prefix" => '' . $this->type_php . ' $' . $this->name,
            "sql_cond" => $this->name == "password" ? "`" . $this->name . "` = PASSWORD(:" . $this->name . ")" : "`" . $this->name . "` = :" . $this->name,
            "sql_param" => $this->name == "password" ? "PASSWORD(:" . $this->name . ")" : ":" . $this->name,
            "bind" => '":' . $this->name . '"' . ' => $' . $this->name,
            "bind_this" => '":' . $this->name . '"' . ' => $this->' . $this->name,
            "this" => '$this->' . $this->name
        );
    }

}
