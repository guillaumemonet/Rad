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
 * Description of DAOTemplateReadTrait
 *
 * @author guillaume
 */
trait DAOTemplateReadTrait {

    public function printRead() {
        $c = StringUtils::println("public function read(" . implode(', ', $this->tableStructure->getPrimaryColumns("php_prefix")) . ",\$use_cache = false) {", 1);
        $c .= StringUtils::println("\$c_key = \"key_" . strtolower($this->className) . "_\"." . implode('."_".', $this->tableStructure->getPrimaryColumns("php")) . ";", 2);
        $c .= StringUtils::println("\$row = null;", 2);
        $c .= StringUtils::println("if(\$use_cache){", 2);
        $c .= StringUtils::println('$row = unserialize(Cache::getHandler()->get($c_key));', 3);
        $c .= StringUtils::println("}", 2);
        $c .= StringUtils::println("if(!isset(\$row) || \$row == null || \$row == false){", 2);
        $c .= StringUtils::println("\$sql = \"SELECT * FROM `$this->tableName` WHERE " . implode(' AND ', $this->tableStructure->getPrimaryColumns("sql_cond")) . ($this->trash ? " AND `trash`=0 " : "") . "\";", 3);
        $c .= StringUtils::println("$this->prepare;", 3);
        $c .= StringUtils::println(sprintf($this->execute, "array(" . implode(",", $this->tableStructure->getPrimaryColumns("bind"))) . ")" . ";", 3);
        $c .= StringUtils::println("\$row = \$result->fetch(\PDO::FETCH_ASSOC);", 3);
        $c .= StringUtils::println("if(\$use_cache){", 3);
        $c .= StringUtils::println('Cache::getHandler()->set($c_key,serialize($row));', 4);
        $c .= StringUtils::println("}", 3);
        $c .= StringUtils::println("}", 2);
        $c .= StringUtils::println("if(is_array(\$row) && count(\$row) > 0){", 2);
        $c .= StringUtils::println("\$this->parse(\$row,\$use_cache);", 3);
        $c .= StringUtils::println("}", 2);
        $c .= StringUtils::println("}", 1);
        return $c;
    }

}
