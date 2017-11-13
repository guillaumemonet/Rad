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

namespace Rad\bin\scripts\templates;

use Rad\Utils\StringUtils;

/**
 * Description of DAOTemplateDeleteTrait
 *
 * @author guillaume
 */
trait DAOTemplateDeleteTrait {

    public function printDelete() {
        $c = StringUtils::println("public function delete() {", 1);
        if (isset($this->tableStructure->columns["trash"])) {
            $c .= StringUtils::println("\$this->trash=true;", 2);
            $c .= StringUtils::println("return \$this->update();", 2);
        } else {
            $c .= StringUtils::println("\$sql = \"DELETE FROM `$this->tableName` WHERE " . implode(' AND ', $this->tableStructure->getPrimaryColumns("sql_cond")) . "\";", 2);
            $c .= StringUtils::println("$this->prepare;", 2);
            $c .= StringUtils::println(sprintf($this->execute, "array(" . implode(",", $this->tableStructure->getPrimaryColumns("bind_this")) . ")") . ";", 2);
        }
        $c .= StringUtils::println("}", 1);
        return $c;
    }

}
