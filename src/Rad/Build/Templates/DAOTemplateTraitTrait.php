<?php
namespace Rad\Build\Templates;
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

use Rad\Utils\StringUtils;

/**
 * Description of DAOTemplateTraitTrait
 *
 * @author guillaume
 */
trait DAOTemplateTraitTrait {

    /**
     * PRIMARY return one object
     * UNIQUE return one object
     * INDEX return array of 0 or N objects
     * SEARCH is fulltext search
     */
    public function printIndexesGetter() {
        $c = "";
        foreach ($this->tableStructure->indexes as $k => $i) {
            if ($i->name == "PRIMARY") {
                $c .= $this->printPrimaryIndexGetter($k, $i);
            } else if ($i->unique) {
                $c .= $this->printUniqueIndexGetter($k, $i);
            } else {
                $c .= $this->printOtherIndexGetter($k, $i);
            }
        }
        return $c;
    }

    public function printPrimaryIndexGetter($k, $i) {
        $c = StringUtils::println("public static function get" . $this->className . "(" . implode(",", $i->getColumns("php_prefix")) . ",\$use_cache=false){", 1);
        $c .= StringUtils::println('$c = new ' . $this->className . '();', 2);
        $c .= StringUtils::println('$c->read(' . implode(",", $i->getColumns("php")) . ',$use_cache);', 2);
        $c .= StringUtils::println('return $c;', 2);
        $c .= StringUtils::println('}', 1);
        return $c;
    }

    public function printUniqueIndexGetter($k, $i) {
        $c = StringUtils::println("public static function " . $k . "(" . implode(",", $i->getColumns("php_prefix")) . ",\$use_cache=false){", 1);
        $c .= StringUtils::println("\$c_key = \"cache_" . $k . "_\"." . implode('."_".', $i->getColumns("php")) . ";", 2);
        $c .= StringUtils::println("\$" . $this->tableName . " = null;", 2);
        $c .= StringUtils::println("if(\$use_cache){", 2);
        $c .= StringUtils::println("\$" . $this->tableName . " = unserialize(Cache::getHandler()->get(\$c_key));", 3);
        $c .= StringUtils::println("}", 2);
        $c .= StringUtils::println("if(!isset(\$" . $this->tableName . ") || \$" . $this->tableName . " == null || \$" . $this->tableName . " == false){", 2);
        $c .= StringUtils::println("\$sql = \"SELECT * FROM " . $this->tableName . " WHERE " . implode(" AND ", $i->getColumns("sql_cond")) . ($this->trash ? " AND trash=0 " : "") . "\";", 3);
        $c .= StringUtils::println("\$result = Database::getHandler()->prepare(\$sql);", 3);
        $c .= StringUtils::println("\$result->execute(array(" . implode(",", $i->getColumns("bind")) . "));", 3);
        $c .= StringUtils::println("\$row = \$result->fetch(\PDO::FETCH_ASSOC);", 3);
        $c .= StringUtils::println("if(is_array(\$row) && count(\$row) > 0){", 3);
        $c .= StringUtils::println("\$$this->tableName = new $this->className();", 4);
        $c .= StringUtils::println("\$" . $this->tableName . "->parse(\$row,\$use_cache);", 4);
        $c .= StringUtils::println("if(\$use_cache){", 4);
        $c .= StringUtils::println("Cache::getHandler()->set(\$c_key,serialize(\$" . $this->tableName . "));", 5);
        $c .= StringUtils::println("}", 4);
        $c .= StringUtils::println("}", 3);
        $c .= StringUtils::println("}", 2);
        $c .= StringUtils::println("return \$$this->tableName;", 2);
        $c .= StringUtils::println("}", 1);
        return $c;
    }

    public function printOtherIndexGetter($k, $i) {
        $c = StringUtils::println("public static function " . $k . "(" . implode(",", $i->getColumns("php_prefix")) . ", \$offset=null,\$limit=null,\$use_cache=false){", 1);
        $c .= StringUtils::println("\$c_key = \"cache_" . $k . "_\"." . implode('."_".', $i->getColumns("php")) . ".\$limit.\"_\".\$offset;", 2);
        $c .= StringUtils::println("\$ret = null;", 2);
        $c .= StringUtils::println("if(\$use_cache){", 2);
        $c .= StringUtils::println("\$ret = unserialize(Cache::getHandler()->get(\$c_key));", 3);
        $c .= StringUtils::println("}", 2);
        $c .= StringUtils::println("if(!isset(\$ret) || \$ret == null || \$ret == false){", 2);
        $c .= StringUtils::println("\$sql = \"SELECT * FROM " . $this->tableName . " WHERE " . implode(",", $i->getColumns("sql_cond")) . ($this->trash ? " AND trash=0 " : "") . "\".(\$offset !==null && \$limit !== null?\" LIMIT \$offset,\$limit\":\"\").\"\";", 3);
        $c .= StringUtils::println("\$result = Database::getHandler()->prepare(\$sql);", 3);
        $c .= StringUtils::println("\$result->execute(array(" . implode(",", $i->getColumns("bind")) . "));", 3);
        $c .= StringUtils::println("\$ret = [];", 3);
        $c .= StringUtils::println("while(\$row = \$result->fetch(\PDO::FETCH_ASSOC)){", 3);
        $c .= StringUtils::println("\$$this->tableName = new $this->className();", 4);
        $c .= StringUtils::println("\$" . $this->tableName . "->parse(\$row,\$use_cache);", 4);
        $c .= StringUtils::println("\$ret[] = \$$this->tableName;", 4);
        $c .= StringUtils::println("}", 3);
        $c .= StringUtils::println("if(\$use_cache){", 3);
        $c .= StringUtils::println("Cache::getHandler()->set(\$c_key,serialize(\$ret));", 4);
        $c .= StringUtils::println("}", 3);
        $c .= StringUtils::println("}", 2);
        $c .= StringUtils::println("return \$ret;", 2);
        $c .= StringUtils::println("}", 1);
        return $c;
    }

    public function printGetAll() {
        $c = StringUtils::println("public static function getAll" . $this->className . "(\$offset = null, \$limit = null,\$use_cache = false){", 1);
        $c .= StringUtils::println("\$c_key = \"key_" . $this->className . "_all_" . $this->tableName . "_\".\$limit.\"_\".\$offset;", 2);
        $c .= StringUtils::println("\$ret = null;", 2);
        $c .= StringUtils::println("if(\$use_cache){", 2);
        $c .= StringUtils::println("\$che = Cache::getHandler()->get(\$c_key);", 3);
        $c .= StringUtils::println("if(isset(\$che)){", 3);
        $c .= StringUtils::println("\$ret = unserialize(\$che);", 4);
        $c .= StringUtils::println("}", 3);
        $c .= StringUtils::println("}", 2);
        $c .= StringUtils::println("if(!isset(\$ret) || \$ret == null || \$ret == false){", 2);
        $c .= StringUtils::println("\$sql = \"SELECT * FROM " . $this->tableName . " WHERE 1 " . ($this->trash ? " AND trash=0 " : "") . " \".(\$offset !==null && \$limit !== null ?\" LIMIT \$offset,\$limit\":\"\").\"\";", 3);
        $c .= StringUtils::println("\$result = Database::getHandler()->prepare(\$sql);", 3);
        $c .= StringUtils::println("\$result->execute();", 3);
        $c .= StringUtils::println("\$ret = [];", 3);
        $c .= StringUtils::println("while(\$row = \$result->fetch(\PDO::FETCH_ASSOC)){", 3);
        $c .= StringUtils::println("\$$this->tableName = new $this->className();", 4);
        $c .= StringUtils::println("\$" . $this->tableName . "->parse(\$row,\$use_cache);", 4);
        $c .= StringUtils::println("\$ret[] = \$$this->tableName;", 4);
        $c .= StringUtils::println("}", 3);
        $c .= StringUtils::println("if(\$use_cache){", 3);
        $c .= StringUtils::println("Cache::getHandler()->set(\$c_key,serialize(\$ret));", 4);
        $c .= StringUtils::println("}", 3);
        $c .= StringUtils::println("}", 2);
        $c .= StringUtils::println("return \$ret;", 2);
        $c .= StringUtils::println("}", 1);
        return $c;
    }

}
