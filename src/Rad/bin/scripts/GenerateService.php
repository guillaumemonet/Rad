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

use Rad\Utils\StringUtils;

/**
 * Description of GenerateService
 *
 * @author guillaume
 */
trait GenerateService {

    private function generateServices() {
        $tables = $this->generateArrayTables();
        foreach ($tables as $name => $table) {
            if (strpos($name, "_has_") !== false) {
                continue;
            }
            $class = StringUtils::camelCase($name);

            $filename = $this->pathService . "/Service" . $class . ".php";
            if (file_exists($filename)) {
                unlink($filename);
            }
            $file = fopen($filename, "w+");

            $sql = "\$sql";
            $id = "\$id";
            $query = "\$result = Database::getHandler()->query(\$sql)";
            $prepare = "\$result = Database::getHandler()->prepare(\$sql)";
            $execute = "\$result->execute(%s)";
            $result = "\$res = \$result->fetchAll(\PDO::FETCH_ASSOC)";


            $trash = false;
            if (isset($table->columns['trash'])) {
                $trash = true;
            }

            $c = StringUtils::printLn("<?php");
            $c .= StringUtils::println("namespace $this->namespaceService;");
            foreach ($this->baseRequire as $require) {
                $c .= "use " . StringUtils::printLn($require . ";");
            }
            $c .= StringUtils::printLn("use $this->namespaceClass\\" . StringUtils::camelCase($name) . ";");
            foreach ($table->onetomany as $ref_table) {
                if ($ref_table["from"] != $class) {
                    $c .= StringUtils::printLn("use $this->namespaceClass\\" . StringUtils::camelCase($ref_table["from"]) . ";");
                }
            }

            foreach ($table->manytomany as $linked_table) {
                if ($linked_table["to"] != $class) {
                    $c .= StringUtils::printLn("use $this->namespaceClass\\" . StringUtils::camelCase($linked_table["to"]) . ";");
                }
            }
            $c .= StringUtils::printLn("abstract class Service$class{");


            $c .= StringUtils::println();
            $c .= StringUtils::println("private function __construct(){", 1);
            $c .= StringUtils::println("}", 1);
            $c .= StringUtils::println();

            //$c .= StringUtils::printLn("}");
            //fwrite($file, $c);


            /**
             * PRIMARY return one object
             * UNIQUE return one object
             * INDEX return array of 0 or N objects
             * SEARCH is fulltext search
             */
            //error_log(print_r($table->indexes, true));
            //exit;
            foreach ($table->indexes as $k => $i) {
                if ($i->name == "PRIMARY") {
                    $c .= StringUtils::println("public static function get" . ucfirst($table->name) . "(" . implode(",", $i->getColumns("php_prefix")) . ",\$use_cache=false){", 1);
                    $c .= StringUtils::println("\$c = new $class();", 2);
                    $c .= StringUtils::println("\$c->read(" . implode(",", $i->getColumns("php")) . ",\$use_cache);", 2);
                    $c .= StringUtils::println("return \$c;", 2);
                    $c .= StringUtils::println("}", 1);
                } else {
                    if ($i->unique) {
                        $c .= StringUtils::println("public static function " . $k . "(" . implode(",", $i->getColumns("php_prefix")) . ",\$use_cache=false){", 1);
                        $c .= StringUtils::println("\$c_key = \"cache_" . $k . "_\"." . implode('."_".', $i->getColumns("php")) . ";", 2);
                        $c .= StringUtils::println("\$" . $table->name . " = null;", 2);
                        $c .= StringUtils::println("if(\$use_cache){", 2);
                        $c .= StringUtils::println("\$" . $table->name . " = unserialize(Cache::getHandler()->get(\$c_key));", 3);
                        $c .= StringUtils::println("}", 2);
                        $c .= StringUtils::println("if(!isset(\$" . $table->name . ") || \$" . $table->name . " == null || \$" . $table->name . " == false){", 2);
                        $c .= StringUtils::println("\$sql = \"SELECT * FROM " . $table->name . " WHERE " . implode(" AND ", $i->getColumns("sql_cond")) . ($trash ? " AND trash=0 " : "") . "\";", 3);
                        $c .= StringUtils::println("\$result = Database::getHandler()->prepare(\$sql);", 3);

                        $c .= StringUtils::println("\$result->execute(array(" . implode(",", $i->getColumns("bind")) . "));", 3);
                        $c .= StringUtils::println("\$row = \$result->fetch(\PDO::FETCH_ASSOC);", 3);
                        $c .= StringUtils::println("if(is_array(\$row) && count(\$row) > 0){", 3);
                        $c .= StringUtils::println("\$$table->name = new $class();", 4);
                        $c .= StringUtils::println("\$" . $table->name . "->parse(\$row,\$use_cache);", 4);
                        $c .= StringUtils::println("if(\$use_cache){", 4);
                        $c .= StringUtils::println("Cache::getHandler()->set(\$c_key,serialize(\$" . $table->name . "));", 5);
                        $c .= StringUtils::println("}", 4);
                        $c .= StringUtils::println("}", 3);
                        $c .= StringUtils::println("}", 2);
                        $c .= StringUtils::println("return \$$table->name;", 2);
                        $c .= StringUtils::println("}", 1);
                    } else {
                        $c .= StringUtils::println("public static function " . $k . "(" . implode(",", $i->getColumns("php_prefix")) . ", \$offset=null,\$limit=null,\$use_cache=false){", 1);
                        $c .= StringUtils::println("\$c_key = \"cache_" . $k . "_\"." . implode('."_".', $i->getColumns("php")) . ".\$limit.\"_\".\$offset;", 2);
                        $c .= StringUtils::println("\$ret = null;", 2);
                        $c .= StringUtils::println("if(\$use_cache){", 2);
                        $c .= StringUtils::println("\$ret = unserialize(Cache::getHandler()->get(\$c_key));", 3);
                        $c .= StringUtils::println("}", 2);
                        $c .= StringUtils::println("if(!isset(\$ret) || \$ret == null || \$ret == false){", 2);
                        $c .= StringUtils::println("\$sql = \"SELECT * FROM " . $table->name . " WHERE " . implode(",", $i->getColumns("sql_cond")) . ($trash ? " AND trash=0 " : "") . "\".(\$offset !==null && \$limit !== null?\" LIMIT \$offset,\$limit\":\"\").\"\";", 3);
                        $c .= StringUtils::println("\$result = Database::getHandler()->prepare(\$sql);", 3);
                        $c .= StringUtils::println("\$result->execute(array(" . implode(",", $i->getColumns("bind")) . "));", 3);
                        $c .= StringUtils::println("\$ret = array();", 3);
                        $c .= StringUtils::println("while(\$row = \$result->fetch(\PDO::FETCH_ASSOC)){", 3);
                        $c .= StringUtils::println("\$$table->name = new $class();", 4);
                        $c .= StringUtils::println("\$" . $table->name . "->parse(\$row,\$use_cache);", 4);
                        $c .= StringUtils::println("\$ret[] = \$$table->name;", 4);
                        $c .= StringUtils::println("}", 3);
                        $c .= StringUtils::println("if(\$use_cache){", 3);
                        $c .= StringUtils::println("Cache::getHandler()->set(\$c_key,serialize(\$ret));", 4);
                        $c .= StringUtils::println("}", 3);
                        $c .= StringUtils::println("}", 2);
                        $c .= StringUtils::println("return \$ret;", 2);
                        $c .= StringUtils::println("}", 1);
                    }
                }
            }

            $c .= StringUtils::println("public static function getAll" . ucfirst($table->name) . "(\$offset = null, \$limit = null,\$use_cache = false){", 1);
            $c .= StringUtils::println("\$c_key = \"key_" . $class . "_all_" . $table->name . "_\".\$limit.\"_\".\$offset;", 2);
            $c .= StringUtils::println("\$ret = null;", 2);
            $c .= StringUtils::println("if(\$use_cache){", 2);
            $c .= StringUtils::println("\$che = Cache::getHandler()->get(\$c_key);", 3);
            $c .= StringUtils::println("if(isset(\$che)){", 3);
            $c .= StringUtils::println("\$ret = unserialize(\$che);", 4);
            $c .= StringUtils::println("}", 3);
            $c .= StringUtils::println("}", 2);
            $c .= StringUtils::println("if(!isset(\$ret) || \$ret == null || \$ret == false){", 2);
            $c .= StringUtils::println("\$sql = \"SELECT * FROM " . $table->name . " WHERE 1 " . ($trash ? " AND trash=0 " : "") . " \".(\$offset !==null && \$limit !== null ?\" LIMIT \$offset,\$limit\":\"\").\"\";", 3);
            $c .= StringUtils::println("\$result = Database::getHandler()->prepare(\$sql);", 3);
            $c .= StringUtils::println("\$result->execute();", 3);
            $c .= StringUtils::println("\$ret = array();", 3);
            $c .= StringUtils::println("while(\$row = \$result->fetch(\PDO::FETCH_ASSOC)){", 3);
            $c .= StringUtils::println("\$$table->name = new $class();", 4);
            $c .= StringUtils::println("\$" . $table->name . "->parse(\$row,\$use_cache);", 4);
            $c .= StringUtils::println("\$ret[] = \$$table->name;", 4);
            $c .= StringUtils::println("}", 3);
            $c .= StringUtils::println("if(\$use_cache){", 3);
            $c .= StringUtils::println("Cache::getHandler()->set(\$c_key,serialize(\$ret));", 4);
            $c .= StringUtils::println("}", 3);
            $c .= StringUtils::println("}", 2);
            $c .= StringUtils::println("return \$ret;", 2);
            $c .= StringUtils::println("}", 1);


            $c .= StringUtils::printLn("}");
            fwrite($file, $c);
        }
    }

}
