<?php
namespace Rad\Build\Templates;

use Rad\Build\Elements\Column;
use Rad\Utils\StringUtils;

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



/**
 * Description of DAOOneToManyTrait
 *
 * @author guillaume
 */
trait DAOOneToManyTrait {

    public function printOneToMany() {
        $c = "";
        foreach ($this->tableStructure->onetomany as $k => $ref_tables) {
            $c .= StringUtils::println("public function get" . str_replace(" ", "", ucwords(str_replace("_", " ", $ref_tables["from"]))) . "(\$use_cache = false){", 1);
            $sql_cols = [];
            $sql_bind = [];
            $cache_cols = [];
            foreach ($ref_tables["ref"] as $cols) {
                $ref_cols = explode("#", $cols);
                $col_from = new Column();
                $col_from->name = $ref_cols[0];

                $col_to = new Column();
                $col_to->name = $ref_cols[1];

                $sql_cols[] = $col_from->getAsVar("sql_cond");
                $sql_bind[] = '"' . $col_from->getAsVar("sql_param") . '"' . "=>" . $col_to->getAsVar("this");
                $cache_cols[] = $col_to->getAsVar("this");
            }

            $c .= StringUtils::println("\$c_key = \"key_" . $ref_tables["to"] . "_ref_" . $ref_tables["from"] . "_\"." . implode('."_".', $cache_cols) . ";", 2);
            $c .= StringUtils::println("\$ret = null;", 2);
            $c .= StringUtils::println("if(\$use_cache){", 2);
            $c .= StringUtils::println("\$che = Cache::getHandler()->get(\$c_key);", 3);
            $c .= StringUtils::println("if(isset(\$che[\$c_key])){", 3);
            $c .= StringUtils::println("\$ret = unserialize(\$che[\$c_key]);", 4);
            $c .= StringUtils::println("}", 3);
            $c .= StringUtils::println("}", 2);
            $c .= StringUtils::println("if(!isset(\$ret) || \$ret == null || \$ret == false){", 2);
            $c .= StringUtils::println("\$sql = \"SELECT * FROM " . $ref_tables["from"] . " WHERE " . implode(" AND ", $sql_cols) . "\";", 3);
            $c .= StringUtils::println("\$result = Database::getHandler()->prepare(\$sql);", 3);
            $c .= StringUtils::println("\$result->execute(array(" . implode(",", $sql_bind) . "));", 3);
            $c .= StringUtils::println("\$ret = [];", 3);
            $c .= StringUtils::println("while(\$row = \$result->fetch(\\PDO::FETCH_ASSOC)){", 3);
            $c .= StringUtils::println("\$" . $ref_tables["from"] . " = new " . StringUtils::camelCase($ref_tables["from"]) . "();", 4);
            $c .= StringUtils::println("\$" . $ref_tables["from"] . "->parse(\$row,\$use_cache);", 4);
            $c .= StringUtils::println("\$ret[] = $" . $ref_tables["from"] . ";", 4);
            $c .= StringUtils::println("}", 3);
            $c .= StringUtils::println("if(\$use_cache){", 3);
            $c .= StringUtils::println("Cache::getHandler()->set(\$c_key,serialize(\$ret));", 4);
            $c .= StringUtils::println("}", 3);
            $c .= StringUtils::println("}", 2);
            $c .= StringUtils::println("return \$ret;", 2);
            $c .= StringUtils::println("}", 1);
            $c .= StringUtils::println("public function add" . StringUtils::camelCase($ref_tables["from"]) . "(" . StringUtils::camelCase($ref_tables["from"]) . " \$" . StringUtils::camelCase($ref_tables["from"]) . "){", 1);
            $c .= StringUtils::println("}", 1);

            $c .= StringUtils::println("public function delete" . StringUtils::camelCase($ref_tables["from"]) . "(" . StringUtils::camelCase($ref_tables["from"]) . " \$" . StringUtils::camelCase($ref_tables["from"]) . "){", 1);
            $c .= StringUtils::println("}", 1);
        }
        return $c;
    }

}
