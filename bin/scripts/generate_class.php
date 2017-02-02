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

namespace Rad\bin\scripts;

use PDO;
use Rad\Database\Database;
use Rad\Utils\StringUtils;

final class generate_class {

    private static $database = null;

    private function __construct() {
        
    }

    public static function generate($database = "bb") {
        self::$database = $database;
        Database::change(self::$database);
        $res_tables = Database::query("SHOW TABLES FROM " . self::$database . " WHERE Tables_in_" . self::$database . " NOT LIKE '%_has_%'");
        //$res_tables = Database::query("SHOW TABLES FROM " . self::$database . " WHERE Tables_in_" . self::$database . " LIKE 'user'");

        while ($tables = $res_tables->fetch()) {
            $table = $tables[0];
            $class = $table;
            $dir = dirname(__FILE__);
            $filename = $dir . "/../../../Shelter/Model/" . $class . ".php";
            if (file_exists($filename)) {
                unlink($filename);
            }

            $file = fopen($filename, "w+");
            $keys = array();
            $columns = array();
            $indexes = array();
            $linked_tables = array();
            $ref_tables = array();

            $sql = "\$sql";
            $id = "\$id";
            $query = "\$result = Database::query(\$sql)";
            $prepare = "\$result = Database::prepare(\$sql)";
            $execute = "\$result->execute(%s)";
            $result = "\$res = \$result->fetchAll(\PDO::FETCH_ASSOC)";

            self::getTableStructure($table, $columns);
            self::getTableIndexes($table, $columns, $indexes);
            self::getManyToMany($table, $linked_tables);
            self::getRefTables($table, $indexes, $ref_tables);

            $trash = false;
            if (isset($columns['trash'])) {
                $trash = true;
            }

            $c = StringUtils::printLn("<?php");
            $c .= StringUtils::println("namespace Shelter\Model;");
            $c .= StringUtils::println("use PDO;");
            $c .= StringUtils::printLn("use Rad\\Model\\Model;");
            $c .= StringUtils::printLn("use Rad\\Database\\Database;");
            $c .= StringUtils::printLn("use Rad\\Cache\\Cache;");
            $c .= StringUtils::printLn("use Rad\\Log\\Log;");
            $c .= StringUtils::printLn("use Rad\\Utils\\StringUtils;");
            $c .= StringUtils::printLn("use Rad\\Encryption\\Encryption;");

            foreach ($ref_tables as $ref_table => $ref_table_datas) {
                if ($ref_table != $class) {
                    $c .= StringUtils::printLn("use Shelter\\Model\\" . $ref_table . ";");
                }
            }

            foreach ($linked_tables as $linked_table) {
                if ($linked_table["to"] != $class) {
                    $c .= StringUtils::printLn("use Shelter\\Model\\" . $linked_table["to"] . ";");
                }
            }
            $c .= StringUtils::printLn("final class $class extends Model {");
            $c .= StringUtils::printLn("public \$resource_name = \"" . $class . "\";", 1);
            $c .= StringUtils::printLn("public \$resource_uri = null;", 1);

            foreach ($columns as $col_name => $col_type) {
                if (StringUtils::endsWith($col_name, "i18n")) {
                    $c .= StringUtils::println("public \$" . str_replace("_i18n", "", $col_name) . "=array();", 1);
                }
                if (StringUtils::endsWith($col_name, "_id_picture")) {
                    $c .= StringUtils::println("public \$" . str_replace("_id_picture", "", $col_name) . "=null;", 1);
                }
                $c .= StringUtils::println("public \$$col_name" . (isset($col_type["default"]) ? "=" . (is_numeric($col_type["default"]) ? $col_type["default"] : "\"" . $col_type["default"] . "\"") : "") . ";", 1);
            }

            $c .= StringUtils::println("private static \$tableFormat =array(", 1);
            foreach ($columns as $col_name => $col_type) {
                $c .= StringUtils::println("\"" . $col_type["name"] . "\"=>" . $col_type["type"] . ",", 2);
            }
            $c .= StringUtils::println(");", 1);


            $c .= StringUtils::println();
            $c .= StringUtils::println("public function __construct(){", 1);
            $c .= StringUtils::println("}", 1);
            $c .= StringUtils::println();

            $c .= StringUtils::println();
            $c .= StringUtils::println("public function read(" . implode(', ', array_column($indexes["PRIMARY"]->columns, 'php_def')) . ",\$use_cache = false) {", 1);
            $c .= StringUtils::println("\$c_key = \"key_" . $class . "-\"." . implode('."-".', array_column($indexes["PRIMARY"]->columns, 'var')) . ";", 2);
            $c .= StringUtils::println("\$row = null;", 2);
            $c .= StringUtils::println("if(\$use_cache){", 2);
            $c .= StringUtils::println("\$row = unserialize(Cache::read(array(\$c_key))[\$c_key]);", 3);
            $c .= StringUtils::println("}", 2);
            $c .= StringUtils::println("if(!isset(\$row) || \$row == null || \$row == false){", 2);
            $c .= StringUtils::println("\$sql = \"SELECT * FROM `$table` WHERE " . implode(' AND ', array_column($indexes["PRIMARY"]->columns, 'sql')) . ($trash ? " AND `trash`=0 " : "") . "\";", 3);
            $c .= StringUtils::println("$prepare;", 3);
            $b_array = self::generateBindArray($indexes["PRIMARY"]->columns, "var");
            $c .= StringUtils::println(sprintf($execute, $b_array) . ";", 3);
            $c .= StringUtils::println("\$row = \$result->fetch(\PDO::FETCH_ASSOC);", 3);
            $c .= StringUtils::println("if(\$use_cache){", 3);
            $c .= StringUtils::println("Cache::write(array(\$c_key=>serialize(\$row)));", 4);
            $c .= StringUtils::println("}", 3);
            $c .= StringUtils::println("}", 2);
            $c .= StringUtils::println("if(is_array(\$row) && count(\$row) > 0){", 2);
            $c .= StringUtils::println("\$this->parse(\$row,\$use_cache);", 3);
            $c .= StringUtils::println("}", 2);
            $c .= StringUtils::println("}", 1);
            $c .= StringUtils::println();

            $c .= StringUtils::println("public function parse(\$row,\$use_cache) {", 1);

            foreach ($columns as $col_name => $col_type) {
                $def = "\"\"";
                if (strlen($col_type["default"]) > 0) {
                    if (is_numeric($col_type["default"])) {
                        $def = $col_type["default"];
                    } else {
                        $def = "\"" . $col_type["default"] . "\"";
                    }
                }
                $c .= StringUtils::println("isset(\$row['$col_name'])?\$this->$col_name=\$row['$col_name']:" . $def . ";", 2);
            }
            $c .= StringUtils::println("\$other_values = array_diff_key(\$row,self::\$tableFormat);", 2);
            $c .= StringUtils::println("if(count(\$other_values) > 0){", 2);
            $c .= StringUtils::println("foreach(\$other_values as \$k=>\$v){", 3);
            $c .= StringUtils::println("if(is_array(\$this->\$k)){", 4);
            $c .= StringUtils::println("\$this->\$k=(array) \$v;", 5);
            $c .= StringUtils::println("}else{", 4);
            $c .= StringUtils::println("\$this->\$k=\$v;", 5);
            $c .= StringUtils::println("}", 4);
            $c .= StringUtils::println("}", 3);
            $c .= StringUtils::println("}", 2);
            foreach ($columns as $column) {
                if (StringUtils::endsWith($column["name"], "_i18n")) {
                    $c .= StringUtils::println("if(\$this->" . str_replace("_i18n", "", $column["name"]) . " == null){", 2);
                    $c .= StringUtils::println("\$ti18ns = i18n_translate::getTranslationFromId(" . $column["this"] . ",null,null,\$use_cache);", 3);
                    $c .= StringUtils::println("foreach(\$ti18ns as \$i18n){", 3);
                    $c .= StringUtils::println("\$this->" . str_replace("_i18n", "", $column["name"]) . "[\$i18n->language_slug] = \$i18n;", 4);
                    $c .= StringUtils::println("}", 3);
                    $c .= StringUtils::println("}", 2);
                }
                if (StringUtils::endsWith($column["name"], "_id_picture")) {
                    $c .= StringUtils::println("if(\$this->" . $column["name"] . " > 0 && \$this->" . str_replace("_id_picture", "", $column["name"]) . " == null){", 2);
                    $c .= StringUtils::println("\$this->" . str_replace("_id_picture", "", $column["name"]) . " = picture::getPicture(\$this->" . $column["name"] . ",\$use_cache);", 3);
                    $c .= StringUtils::println("}", 2);
                }
            }
            $c .= StringUtils::println("\$this->generateResourceURI();", 2);
            $c .= StringUtils::println("}", 1);



            $c .= StringUtils::println("public function create(\$force=false){", 1);
            if (isset($columns["slug"]) && isset($columns["name"])) {
                $c .= StringUtils::println("if(\$this->slug == null){", 2);
                $c .= StringUtils::println("\$this->slug=StringUtils::slugify(\$this->name);", 3);
                $c .= StringUtils::println("}", 2);
            }
            foreach ($columns as $column) {
                if (StringUtils::endsWith($column["name"], "_i18n")) {
                    $c .= StringUtils::println("if(" . $column["this"] . " == null){", 2);
                    $c .= StringUtils::println("\$li18n = new i18n();", 3);
                    $c .= StringUtils::println("\$li18n->name = \"" . $table . "_" . $column["name"] . "\";", 3);
                    $c .= StringUtils::println("\$li18n->table= \"" . $table . "\";", 3);
                    $c .= StringUtils::println("\$li18n->row= \"" . $column["name"] . "\";", 3);
                    $c .= StringUtils::println("\$li18n->create();", 3);
                    $c .= StringUtils::println($column["this"] . " = \$li18n->getID();", 3);
                    $c .= StringUtils::println("foreach(\$this->" . str_replace("_i18n", "", $column["name"]) . " as \$lang=>\$datas){", 3);
                    $c .= StringUtils::println("\$ti18n = new i18n_translate();", 4);
                    $c .= StringUtils::println("\$ti18n->language_slug = \$lang;", 4);
                    $c .= StringUtils::println("\$ti18n->i18n_id_i18n = " . $column["this"] . ";", 4);
                    $c .= StringUtils::println("\$ti18n->datas=\$datas;", 4);
                    $c .= StringUtils::println("\$ti18n->create();", 4);
                    $c .= StringUtils::println("}", 3);
                    $c .= StringUtils::println("}", 2);
                }
            }
            $c .= StringUtils::println("if(\$force){", 2);
            $c .= StringUtils::println("$sql = \"INSERT INTO `$table` (" . (count($indexes["PRIMARY"]->columns) > 0 ? implode(',', array_column($indexes["PRIMARY"]->columns, 'champ')) . "," : "") . implode(',', array_column($columns, 'champ')) . ") VALUES (" . (count($indexes["PRIMARY"]->columns) > 0 ? implode(',', array_column($indexes["PRIMARY"]->columns, 'param')) . "," : "") . implode(',', array_column($columns, 'param')) . ")\";", 3);
            $c .= StringUtils::println("$prepare;", 3);
            $b_array = self::generateBindArrayValue($indexes["PRIMARY"]->columns, "this", $columns, "this");
            foreach ($b_array as $b) {
                $c .= StringUtils::println("\$result->bindValue($b);", 3);
            }
            $c .= StringUtils::println("}else{", 2);

            $tkeys = $indexes["PRIMARY"]->columns;
            $tcolumns = $columns;
            foreach ($tkeys as $col_name => $col_type) {
                if ($col_type["auto"] > 0) {
                    $c .= StringUtils::println("\$this->$col_name = null;", 3);
                    unset($tkeys[$col_name]);
                    unset($tcolumns[$col_name]);
                    break;
                }
            }
            $c .= StringUtils::println("$sql = \"INSERT INTO `$table` (" . (count($tkeys) > 0 ? implode(',', array_column($tkeys, 'champ')) . "," : "") . implode(',', array_column($tcolumns, 'champ')) . ") VALUES (" . (count($tkeys) > 0 ? implode(',', array_column($tkeys, 'param')) . "," : "") . implode(',', array_column($tcolumns, 'param')) . ")\";", 3);
            $c .= StringUtils::println("$prepare;", 3);
            $b_array = self::generateBindArrayValue($tkeys, "this", $tcolumns, "this");
            foreach ($b_array as $b) {
                $c .= StringUtils::println("\$result->bindValue($b);", 3);
            }
            $c .= StringUtils::println("}", 2);
            $c .= StringUtils::println("if(" . sprintf($execute, "") . " === false){", 2);
            $c .= StringUtils::println("Log::error(\$result->errorInfo());", 3);
            $c .= StringUtils::println("return false;", 3);
            $c .= StringUtils::println("}else{", 2);
            foreach ($indexes["PRIMARY"]->columns as $col_name => $col_type) {
                if ($col_type["auto"] > 0) {
                    $c .= StringUtils::println("\$this->$col_name = (" . $col_type['php'] . ") Database::lastInsertId();", 3);
                    $c .= StringUtils::println("\$this->generateResourceURI();", 3);
                    break;
                }
            }
            if (isset($columns["password"])) {
                $c .= StringUtils::println("\$this->password=Encryption::password(\$this->password);", 3);
            }

            $c .= StringUtils::println("return true;", 3);
            $c .= StringUtils::println("}", 2);
            $c .= StringUtils::println("}", 1);


            $c .= StringUtils::println("public function delete() {", 1);
            if (isset($trash)) {
                $c .= StringUtils::println("\$this->trash=true;", 2);
                $c .= StringUtils::println("return \$this->update();", 2);
            } else {
                $c .= StringUtils::println("$sql = \"DELETE FROM `$table` WHERE " . implode(' AND ', array_column($indexes["PRIMARY"]->columns, 'sql')) . "\";", 2);
                $c .= StringUtils::println("$prepare;", 2);
                $b_array = self::generateBindArray($indexes["PRIMARY"]->columns, "this");
                $c .= StringUtils::println(sprintf($execute, $b_array) . ";", 2);
            }
            $c .= StringUtils::println("}", 1);


            $c .= StringUtils::println("public function update(){", 1);
            if (isset($columns["slug"]) && isset($columns["name"])) {
                $c .= StringUtils::println("\$this->slug=StringUtils::slugify(\$this->slug);", 2);
            }
            $tcolumns = $columns;
            unset($tcolumns["password"]);
            $c .= StringUtils::println("$sql = \"UPDATE `$table` SET " . implode(",\n\t\t", array_column($tcolumns, 'sql')) . " WHERE " . implode(" AND \n\t\t", array_column($keys, 'sql')) . "\";", 2);
            $c .= StringUtils::println("$prepare;", 2);
            $b_array = self::generateBindArrayValue($indexes["PRIMARY"]->columns, "this", $tcolumns, "this");
            foreach ($b_array as $b) {
                $c .= StringUtils::println("\$result->bindValue($b);", 2);
            }
            foreach ($columns as $column) {
                if (StringUtils::endsWith($column["name"], "_i18n")) {
                    //TODO Rajouter les conditions de test pour la creation d'une trad
                    $c .= StringUtils::println("foreach(\$this->" . str_replace("_i18n", "", $column["name"]) . " as \$lang=>\$datas){", 2);
                    $c .= StringUtils::println("\$ti18n = i18n_translate->getTranslation(" . $column["this"] . ",$lang);", 3);
                    $c .= StringUtils::println("\$ti18n->datas=\$datas;", 3);
                    $c .= StringUtils::println("\$ti18n->update();", 3);
                    $c .= StringUtils::println("}", 2);
                }
            }

            $c .= StringUtils::println("return " . sprintf($execute, "") . ";", 2);
            $c .= StringUtils::println("}", 1);
            if (isset($columns["name"])) {
                $c .= StringUtils::println("public function __toString(){", 1);
                $c .= StringUtils::println("return \$this->name;", 2);
                $c .= StringUtils::println("}", 1);
            }

            foreach ($indexes["PRIMARY"]->columns as $col_name => $col_type) {
                if ($col_type["auto"] > 0) {
                    $c .= StringUtils::println("public function getID(){", 1);
                    $c .= StringUtils::println("return \$this->$col_name;", 2);
                    $c .= StringUtils::println("}", 1);
                    break;
                }
            }



            /**
             * PRIMARY return one object
             * UNIQUE return one object
             * INDEX return array of 0 or N objects
             * SEARCH is fulltext search
             */
            foreach ($indexes as $k => $i) {
                if ($i->name == "PRIMARY") {
                    $c .= StringUtils::println("public static function get" . ucfirst($table) . "(" . implode(",", array_column($i->columns, 'var')) . ",\$use_cache=false){", 1);
                    $c .= StringUtils::println("\$c = new $table();", 2);
                    $c .= StringUtils::println("\$c->read(" . implode(",", array_column($i->columns, 'var')) . ",\$use_cache);", 2);
                    $c .= StringUtils::println("return \$c;", 2);
                    $c .= StringUtils::println("}", 1);
                } else {
                    if ($i->unique) {
                        $c .= StringUtils::println("public static function " . $k . "(" . implode(",", array_column($i->columns, "var")) . ",\$use_cache=false){", 1);
                        $c .= StringUtils::println("\$c_key = \"cache_" . $k . "_\"." . implode(".\"_\".", array_column($i->columns, "var")) . ";", 2);
                        $c .= StringUtils::println("\$" . $class . " = null;", 2);
                        $c .= StringUtils::println("if(\$use_cache){", 2);
                        $c .= StringUtils::println("\$" . $class . " = unserialize(Cache::read(array(\$c_key))[\$c_key]);", 3);
                        $c .= StringUtils::println("}", 2);
                        $c .= StringUtils::println("if(!isset(\$" . $class . ") || \$" . $class . " == null || \$" . $class . " == false){", 2);
                        $c .= StringUtils::println("\$sql = \"SELECT * FROM " . $table . " WHERE " . implode(" AND ", array_column($i->columns, "sql")) . ($trash ? " AND trash=0 " : "") . "\";", 3);
                        $c .= StringUtils::println("\$result = Database::prepare(\$sql);", 3);
                        $b_array = self::generateBindArrayValue($i->columns, "var");
                        foreach ($b_array as $b) {
                            $c .= StringUtils::println("\$result->bindValue($b);", 3);
                        }
                        $c .= StringUtils::println("\$result->execute();", 3);
                        $c .= StringUtils::println("\$row = \$result->fetch(\PDO::FETCH_ASSOC);", 3);
                        $c .= StringUtils::println("if(is_array(\$row) && count(\$row) > 0){", 3);
                        $c .= StringUtils::println("\$$class = new $class();", 4);
                        $c .= StringUtils::println("\$" . $class . "->parse(\$row,\$use_cache);", 4);
                        $c .= StringUtils::println("if(\$use_cache){", 4);
                        $c .= StringUtils::println("Cache::write(array(\$c_key=>serialize(\$" . $class . ")));", 5);
                        $c .= StringUtils::println("}", 4);
                        $c .= StringUtils::println("}", 3);
                        $c .= StringUtils::println("}", 2);
                        $c .= StringUtils::println("return \$$class;", 2);
                        $c .= StringUtils::println("}", 1);
                    } else {
                        $c .= StringUtils::println("public static function " . $k . "(" . implode(",", array_column($i->columns, "var")) . ", \$offset=null,\$limit=null,\$use_cache=false){", 1);
                        $c .= StringUtils::println("\$c_key = \"cache_" . $k . "_\"." . implode(".\"_\".", array_column($i->columns, "var")) . ".\$limit.\"_\".\$offset;", 2);
                        $c .= StringUtils::println("\$ret = null;", 2);
                        $c .= StringUtils::println("if(\$use_cache){", 2);
                        $c .= StringUtils::println("\$ret = unserialize(Cache::read(array(\$c_key))[\$c_key]);", 3);
                        $c .= StringUtils::println("}", 2);
                        $c .= StringUtils::println("if(!isset(\$ret) || \$ret == null || \$ret == false){", 2);
                        $c .= StringUtils::println("\$sql = \"SELECT * FROM " . $table . " WHERE " . implode(" AND ", array_column($i->columns, "sql")) . ($trash ? " AND trash=0 " : "") . "\".(\$offset !==null && \$limit !== null?\" LIMIT \$offset,\$limit\":\"\").\"\";", 3);
                        $c .= StringUtils::println("\$result = Database::prepare(\$sql);", 3);
                        $b_array = self::generateBindArrayValue($i->columns, "var");
                        foreach ($b_array as $b) {
                            $c .= StringUtils::println("\$result->bindValue($b);", 3);
                        }
                        $c .= StringUtils::println("\$result->execute();", 3);
                        $c .= StringUtils::println("\$ret = array();", 3);
                        $c .= StringUtils::println("while(\$row = \$result->fetch(\PDO::FETCH_ASSOC)){", 3);
                        $c .= StringUtils::println("\$$class = new $class();", 4);
                        $c .= StringUtils::println("\$" . $class . "->parse(\$row,\$use_cache);", 4);
                        $c .= StringUtils::println("\$ret[] = \$$class;", 4);
                        $c .= StringUtils::println("}", 3);
                        $c .= StringUtils::println("if(\$use_cache){", 3);
                        $c .= StringUtils::println("Cache::write(array(\$c_key=>serialize(\$ret)));", 4);
                        $c .= StringUtils::println("}", 3);
                        $c .= StringUtils::println("}", 2);
                        $c .= StringUtils::println("return \$ret;", 2);
                        $c .= StringUtils::println("}", 1);
                    }
                }
            }


            foreach ($ref_tables as $ref_table => $ref_rows) {
                $c .= StringUtils::println("public function getRef" . str_replace(" ", "", ucwords(str_replace("_", " ", $ref_table))) . "(\$use_cache = false){", 1);
                $trs = explode(",", $ref_rows);
                $w = array();
                $ch = array();
                foreach ($trs as $tr) {
                    $tmp = explode("#", $tr);
                    $w[] = $tmp[0] . "=\".\$this->" . $tmp[1] . ".\"";
                    $ch[] = "\$this->" . $tmp[1];
                }
                $c .= StringUtils::println("\$c_key = \"key_" . $class . "_ref_" . $ref_table . "_\"." . implode('."-".', $ch) . ";", 2);
                $c .= StringUtils::println("\$ret = null;", 2);
                $c .= StringUtils::println("if(\$use_cache){", 2);
                $c .= StringUtils::println("\$che = Cache::read(array(\$c_key));", 3);
                $c .= StringUtils::println("if(isset(\$che[\$c_key])){", 3);
                $c .= StringUtils::println("\$ret = unserialize(\$che[\$c_key]);", 4);
                $c .= StringUtils::println("}", 3);
                $c .= StringUtils::println("}", 2);
                $c .= StringUtils::println("if(!isset(\$ret) || \$ret == null || \$ret == false){", 2);
                $c .= StringUtils::println("\$sql = \"SELECT * FROM $ref_table WHERE " . implode(" AND ", $w) . "\";", 3);
                $c .= StringUtils::println("\$result = Database::query(\$sql);", 3);
                $c .= StringUtils::println("\$ret = array();", 3);
                $c .= StringUtils::println("while(\$row = \$result->fetch(\\PDO::FETCH_ASSOC)){", 3);
                $c .= StringUtils::println("\$" . $ref_table . " = new " . $ref_table . "();", 4);
                $c .= StringUtils::println("\$" . $ref_table . "->parse(\$row,\$use_cache);", 4);
                $c .= StringUtils::println("\$ret[] = $" . $ref_table . ";", 4);
                $c .= StringUtils::println("}", 3);
                $c .= StringUtils::println("if(\$use_cache){", 3);
                $c .= StringUtils::println("Cache::write(array(\$c_key=>serialize(\$ret)));", 4);
                $c .= StringUtils::println("}", 3);
                $c .= StringUtils::println("}", 2);
                $c .= StringUtils::println("return \$ret;", 2);
                $c .= StringUtils::println("}", 1);
                $c .= StringUtils::println("public function addRef($ref_table \$$ref_table){", 1);
                $c .= StringUtils::println("}", 1);

                $c .= StringUtils::println("public function deleteRef($ref_table \$$ref_table){", 1);
                $c .= StringUtils::println("}", 1);
            }

            $c .= StringUtils::println("public static function getAll" . ucfirst($table) . "(\$offset = null, \$limit = null,\$use_cache = false){", 1);
            $c .= StringUtils::println("\$c_key = \"key_" . $class . "_all_" . $table . "_\".\$limit.\"_\".\$offset;", 2);
            $c .= StringUtils::println("\$ret = null;", 2);
            $c .= StringUtils::println("if(\$use_cache){", 2);
            $c .= StringUtils::println("\$che = Cache::read(array(\$c_key));", 3);
            $c .= StringUtils::println("if(isset(\$che[\$c_key])){", 3);
            $c .= StringUtils::println("\$ret = unserialize(\$che[\$c_key]);", 4);
            $c .= StringUtils::println("}", 3);
            $c .= StringUtils::println("}", 2);
            $c .= StringUtils::println("if(!isset(\$ret) || \$ret == null || \$ret == false){", 2);
            $c .= StringUtils::println("\$sql = \"SELECT * FROM " . $table . " WHERE 1 " . ($trash ? " AND trash=0 " : "") . " \".(\$offset !==null && \$limit !== null ?\" LIMIT \$offset,\$limit\":\"\").\"\";", 3);
            $c .= StringUtils::println("\$result = Database::prepare(\$sql);", 3);
            $c .= StringUtils::println("\$result->execute();", 3);
            $c .= StringUtils::println("\$ret = array();", 3);
            $c .= StringUtils::println("while(\$row = \$result->fetch(\PDO::FETCH_ASSOC)){", 3);
            $c .= StringUtils::println("\$$class = new $class();", 4);
            $c .= StringUtils::println("\$" . $class . "->parse(\$row,\$use_cache);", 4);
            $c .= StringUtils::println("\$ret[] = \$$class;", 4);
            $c .= StringUtils::println("}", 3);
            $c .= StringUtils::println("if(\$use_cache){", 3);
            $c .= StringUtils::println("Cache::write(array(\$c_key=>serialize(\$ret)));", 4);
            $c .= StringUtils::println("}", 3);
            $c .= StringUtils::println("}", 2);
            $c .= StringUtils::println("return \$ret;", 2);
            $c .= StringUtils::println("}", 1);


            $c .= StringUtils::printLn("}");
            fwrite($file, $c);
        }
    }

    public static function getTableIndexes($table, $columns, &$indexes) {
        $sql = "SHOW KEYS FROM `$table` WHERE 1"; //Key_name <> 'PRIMARY'";
        $rows = Database::query($sql);
        while ($tkey = $rows->fetch()) {
            if (!isset($indexes[$tkey["Key_name"]])) {
                $index = new Index();
                $index->name = $tkey["Key_name"];
                $index->unique = !$tkey["Non_unique"];
                $index->columns[$tkey["Column_name"]] = $columns[$tkey["Column_name"]];
                $indexes[$tkey["Key_name"]] = $index;
            } else {
                $indexes[$tkey["Key_name"]]->columns[$tkey["Column_name"]] = $columns[$tkey["Column_name"]];
            }
        }
    }

    public static function getTableStructure($table, &$columns) {
        $result = Database::query("SHOW FULL COLUMNS FROM `$table`;");
        while ($row = $result->fetch()) {

            $col_name = $row["Field"];
            $col_type = null;
            $col_php = null;

            if (strstr($row["Type"], "char") !== false || strstr($row["Type"], "text") !== false) {
                $col_type = "\\PDO::PARAM_STR";
                $col_php = "string";
            } else if (strstr($row["Type"], "tinyint") !== false) {
                $col_type = "\\PDO::PARAM_INT";
                $col_php = "boolan";
            } else if (strstr($row["Type"], "blob") !== false) {
                $col_type = "\\PDO::PARAM_LOB";
                $col_php = "binary";
            } else if (strstr($row["Type"], "int") !== false) {
                $col_type = "\\PDO::PARAM_INT";
                $col_php = "int";
            } else if (strstr("float", $row["Type"]) !== false || strstr("long", $row["Type"]) !== false || strstr("double", $row["Type"]) !== false) {
                $col_type = "\\PDO::PARAM_STR";
                $col_php = "decimal";
            } else {
                $col_type = "\\PDO::PARAM_STR";
                $col_php = "string";
            }
            $col_champ = "`" . $col_name . "`";
            $col_str = "'" . $col_name . "'";
            $col_this = "\$this->" . $col_name;
            $col_var = "\$" . $col_name;
            $col_sql = "`" . $col_name . "` = :" . $col_name;
            $col_param = ":" . $col_name;
            if ($col_name == "password") {
                $col_sql = "`" . $col_name . "` = PASSWORD( :" . $col_name . " )";
                $col_param = "PASSWORD( :" . $col_name . " )";
            }
            $col_bind = ":" . $col_name;
            $col_default = $row["Default"];
            $col_def = "\$" . $col_name . " = " . (isset($col_default) ? $col_default : "null");
            $col_auto = 0;
            if (isset($row["Extra"]) && $row["Extra"] == "auto_increment") {
                $col_auto = 1;
            }
            $columns[$col_name] = array("name" => $col_name, "type" => $col_type, "php" => $col_php, "champ" => $col_champ, "this" => $col_this, "var" => $col_var, "sql" => $col_sql, "auto" => $col_auto, "param" => $col_param, "default" => $col_default, "string" => $col_str, "php_def" => $col_def, "bind" => $col_bind);
        }
    }

    /**
     * $ks = self::search_keys($indexes, $col_name);
      foreach ($ks as $t) {
      if (isset($keys[$col_name])) {
      $indexes[$t[0]][$col_name] = $keys[$col_name];
      } else {
      $indexes[$t[0]][$col_name] = $columns[$col_name];
      }
      }
     */
    public static function getManyToMany($table, &$tables) {
        $sql = "SHOW TABLES FROM bb WHERE Tables_in_bb LIKE '" . $table . "_has_%'";
        $link_tables = Database::query($sql);
        while ($link_table = $link_tables->fetch(PDO::FETCH_NUM)) {
            $ext = str_replace($table . "_has_", "", $link_table[0]);
            $tables[] = array("from" => $table, "by" => $link_table[0], "to" => $ext);
        }
        return $tables;
    }

    public static function getRefTables($table, $indexes, &$tables) {
        $fk_tables = "SELECT TABLE_NAME,GROUP_CONCAT(DISTINCT CONCAT(COLUMN_NAME,'#',REFERENCED_COLUMN_NAME)) as COLS
FROM
  information_schema.KEY_COLUMN_USAGE
WHERE
  REFERENCED_TABLE_NAME = '$table'
  AND REFERENCED_COLUMN_NAME IN (" . implode(",", array_column($indexes["PRIMARY"]->columns, 'string')) . ")
      AND TABLE_NAME NOT LIKE \"%_has_%\"
  AND TABLE_SCHEMA = 'bb' GROUP BY TABLE_NAME;";
        $link_tables = Database::query($fk_tables);
        while ($link_table = $link_tables->fetch()) {
            $tables[$link_table["TABLE_NAME"]] = $link_table["COLS"];
        }
        return $tables;
    }

    public static function generateColumnType($indexes_array, $type) {
        
    }

    public static function generateBindArray($keys, $k_type, $columns = null, $c_type = null) {
        $tmp_array = array();
        foreach ($keys as $k) {
            $tmp_array[] = "'" . $k["bind"] . "'=>" . $k[$k_type];
        }
        if ($columns !== null) {
            foreach ($columns as $c) {
                $tmp_array[] = "'" . $c["bind"] . "'=>" . $c[$c_type];
            }
        }
        return "array(" . implode(",", $tmp_array) . ")";
    }

    public static function generateBindArrayValue($keys, $k_type, $columns = null, $c_type = null) {
        $tmp_array = array();
        foreach ($keys as $k) {
            $tmp_array[] = "'" . $k["bind"] . "'," . $k[$k_type] . "," . $k["type"];
        }
        if ($columns !== null) {
            foreach ($columns as $c) {
                $tmp_array[] = "'" . $c["bind"] . "'," . $c[$c_type] . "," . $c["type"];
            }
        }
        return $tmp_array;
    }

    public static function search_keys($ar, $sr) {
        $ret = array();
        foreach ($ar as $k => $v) {
            foreach ($v as $k1 => $v1) {
                if ($k1 == $sr) {
                    $ret[] = array($k, $k1);
                }
            }
        }
        return $ret;
    }

}

class Index {

    public $name;
    public $unique;
    public $columns;

}
