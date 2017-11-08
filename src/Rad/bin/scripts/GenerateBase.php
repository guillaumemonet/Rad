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

final class GenerateBase {

    private $database = null;
    private $pathClass = null;
    private $pathService = null;
    private $pathImp = null;
    private $namespaceClass = null;
    private $namespaceService = null;
    private $namespaceImp = null;
    private $baseRequire = array(
        "PDO",
        "Rad\\Model\\Model",
        "Rad\\Model\\ModelDAO",
        "Rad\\Database\\Database",
        "Rad\\Cache\\Cache",
        "Rad\\Log\\Log",
        "Rad\\Utils\\StringUtils",
        "Rad\\Encryption\\Encryption"
    );
    private $i18n_translate_table = "i18n_translate";
    private $i18n_table = "i18n";
    private $i18n_translate_class;
    private $i18n_class;
    private $picture_table = "picture";
    private $picture_class;
    private $language_table = "language";
    private $language_class;

    public function __construct($database = "bb", $dir = "", $basePath = "", $prefixClassesPath = "", $prefixServicesPath = "", $prefixImpsPath = "") {
        $this->database = $database;
        $this->pathClass = $basePath . "/" . $prefixClassesPath;
        $this->namespaceClass = rtrim(str_replace("/", "\\", $this->pathClass), "\\");
        $this->pathService = $basePath . "/" . $prefixServicesPath;
        $this->namespaceService = rtrim(str_replace("/", "\\", $this->pathService), "\\");
        $this->pathImp = $basePath . "/" . $prefixImpsPath;
        $this->namespaceImp = rtrim(str_replace("/", "\\", $this->pathImp), "\\");
        $this->i18n_class = StringUtils::camelCase($this->i18n_table);
        $this->i18n_translate_class = StringUtils::camelCase($this->i18n_translate_table);
        $this->picture_class = StringUtils::camelCase($this->picture_table);
        $this->language_class = StringUtils::camelCase($this->language_table);
        $this->pathClass = $dir . "/" . $this->pathClass;
        $this->pathImp = $dir . "/" . $this->pathImp;
        $this->pathService = $dir . "/" . $this->pathService;
    }

    public function generate(bool $generateImp = false) {
        mkdir($this->pathClass, 0777, true);
        mkdir($this->pathService, 0777, true);
        $this->generateClass();
        $this->generateServices();
        if ($generateImp) {
            $this->generateImp();
        }
        $this->generateController();
    }

    private function generateArrayTables() {
        Database::getHandler()->change($this->database);
        $sql = "SHOW TABLES FROM " . $this->database;
        error_log($sql);
        $res_tables = Database::getHandler()->query($sql);

        $tables = array();
        while ($row = $res_tables->fetch()) {
            $table = new Table();
            $table->name = $row[0];
            $table->columns = $this->getTableStructure($table->name);
            $table->indexes = $this->getTableIndexes($table->name, $table->columns);
            $table->onetomany = $this->getOneToManyTable($table->name);
            $table->manytomany = $this->getManyToManyTable($table->name);
            $tables[$table->name] = $table;
        }
        return $tables;
    }

    private function generateClass() {
        $tables = $this->generateArrayTables();
        foreach ($tables as $name => $table) {
            if (strpos($name, "_has_") !== false) {
                continue;
            }
            $class = StringUtils::camelCase($name);

            $filename = $this->pathClass . "/" . $class . ".php";
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
            $c .= StringUtils::println("namespace $this->namespaceClass;");
            foreach ($this->baseRequire as $require) {
                $c .= "use " . StringUtils::printLn($require . ";");
            }

            $c .= "use " . StringUtils::println($this->namespaceService . "\\Service" . StringUtils::camelCase($this->i18n_translate_class) . ";");

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
            $c .= StringUtils::printLn("class " . $class . " extends Model {");
            $c .= StringUtils::printLn("public \$resource_name = \"" . $class . "\";", 1);
            $c .= StringUtils::printLn("public \$resource_uri = null;", 1);
            $c .= StringUtils::printLn("public \$resource_namespace = __NAMESPACE__;", 1);
            foreach ($table->columns as $col_name => $col) {
                if (StringUtils::endsWith($col_name, "i18n") && $col->auto == 0 && !StringUtils::startsWith($table->name, "i18n")) {
                    $c .= StringUtils::println("public \$" . str_replace("_i18n", "", $col_name) . "=array();", 1);
                }
                if (StringUtils::endsWith($col_name, "_id_picture")) {
                    $c .= StringUtils::println("public \$" . str_replace("_id_picture", "", $col_name) . "=null;", 1);
                }
                $c .= StringUtils::println("public \$$col_name" . (isset($col->default) ? "=" . (is_numeric($col->default) ? $col->default : "\"" . $col->default . "\"") : "") . ";", 1);
            }

            $c .= StringUtils::println("private static \$tableFormat =array(", 1);
            foreach ($table->columns as $col_name => $col) {
                $c .= StringUtils::println("\"" . $col_name . "\"=>" . $col->type_sql . ",", 2);
            }
            $c .= StringUtils::println(");", 1);


            $c .= StringUtils::println();
            $c .= StringUtils::println("public function __construct(){", 1);
            $c .= StringUtils::println("}", 1);
            $c .= StringUtils::println();

            $c .= StringUtils::println();
            $c .= StringUtils::println("public function read(" . implode(', ', $table->getPrimaryColumns("php_prefix")) . ",\$use_cache = false) {", 1);
            $c .= StringUtils::println("\$c_key = \"key_" . strtolower($class) . "_\"." . implode('."_".', $table->getPrimaryColumns("php")) . ";", 2);
            $c .= StringUtils::println("\$row = null;", 2);
            $c .= StringUtils::println("if(\$use_cache){", 2);
            $c .= StringUtils::println('$row = unserialize(Cache::getHandler()->get($c_key));', 3);
            $c .= StringUtils::println("}", 2);
            $c .= StringUtils::println("if(!isset(\$row) || \$row == null || \$row == false){", 2);
            $c .= StringUtils::println("\$sql = \"SELECT * FROM `$name` WHERE " . implode(' AND ', $table->getPrimaryColumns("sql_cond")) . ($trash ? " AND `trash`=0 " : "") . "\";", 3);
            $c .= StringUtils::println("$prepare;", 3);
            $c .= StringUtils::println(sprintf($execute, "array(" . implode(",", $table->getPrimaryColumns("bind"))) . ")" . ";", 3);
            $c .= StringUtils::println("\$row = \$result->fetch(\PDO::FETCH_ASSOC);", 3);
            $c .= StringUtils::println("if(\$use_cache){", 3);
            $c .= StringUtils::println('Cache::getHandler()->set($c_key,serialize($row));', 4);
            $c .= StringUtils::println("}", 3);
            $c .= StringUtils::println("}", 2);
            $c .= StringUtils::println("if(is_array(\$row) && count(\$row) > 0){", 2);
            $c .= StringUtils::println("\$this->parse(\$row,\$use_cache);", 3);
            $c .= StringUtils::println("}", 2);
            $c .= StringUtils::println("}", 1);
            $c .= StringUtils::println();

            $c .= StringUtils::println("public function parse(\$row,\$use_cache) {", 1);

            foreach ($table->columns as $col_name => $col) {
                $def = "\"\"";
                if (strlen($col->default) > 0) {
                    if (is_numeric($col->default)) {
                        $def = $col->default;
                    } else {
                        $def = '"' . $col->default . '"';
                    }
                }
                $c .= StringUtils::println("\$this->$col_name=isset(\$row['$col_name'])?\$row['$col_name']:" . $def . ";", 2);
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
            foreach ($table->columns as $col_name => $col) {
                if (StringUtils::endsWith($col_name, "_i18n") && $col->auto == 0 && !StringUtils::startsWith($table->name, "i18n")) {
                    $c .= StringUtils::println("if(\$this->" . str_replace("_i18n", "", $col_name) . " == null){", 2);
                    $c .= StringUtils::println("\$ti18ns = Service" . $this->i18n_translate_class . "::getTranslationFromId(" . $col->getAsVar("this") . ",null,null,\$use_cache);", 3);
                    $c .= StringUtils::println("foreach(\$ti18ns as \$i18n){", 3);
                    $c .= StringUtils::println("\$this->" . str_replace("_i18n", "", $col_name) . "[\$i18n->language_slug] = \$i18n;", 4);
                    $c .= StringUtils::println("}", 3);
                    $c .= StringUtils::println("}", 2);
                }
                if (StringUtils::endsWith($col_name, "_id_picture")) {
                    $c .= StringUtils::println("if(\$this->" . $col_name . " > 0 && \$this->" . str_replace("_id_picture", "", $col_name) . " == null){", 2);
                    $c .= StringUtils::println("\$this->" . str_replace("_id_picture", "", $col_name) . " = Picture::getPicture(" . $col->getAsVar("this") . ",\$use_cache);", 3);
                    $c .= StringUtils::println("}", 2);
                }
            }
            $c .= StringUtils::println("\$this->generateResourceURI();", 2);
            $c .= StringUtils::println("}", 1);



            $c .= StringUtils::println("public function create(\$force=false){", 1);
            if (isset($table->columns["slug"]) && isset($table->columns["name"])) {
                $c .= StringUtils::println("if(\$this->slug == null){", 2);
                $c .= StringUtils::println("\$this->slug=StringUtils::slugify(\$this->name);", 3);
                $c .= StringUtils::println("}", 2);
            }
            foreach ($table->columns as $col_name => $col) {
                if (StringUtils::endsWith($col_name, "_i18n") && $col->auto == 0 && !StringUtils::startsWith($table->name, "i18n")) {
                    $c .= StringUtils::println("if(" . $col->getAsVar("this") . " == null){", 2);
                    $c .= StringUtils::println("\$li18n = new " . $this->i18n_class . "();", 3);
                    $c .= StringUtils::println("\$li18n->name = \"" . $table->name . "_" . $col_name . "\";", 3);
                    $c .= StringUtils::println("\$li18n->table= \"" . $table->name . "\";", 3);
                    $c .= StringUtils::println("\$li18n->row= \"" . $col_name . "\";", 3);
                    $c .= StringUtils::println("\$li18n->create();", 3);
                    $c .= StringUtils::println($col->getAsVar("this") . " = \$li18n->getID();", 3);
                    $c .= StringUtils::println("foreach(\$this->" . str_replace("_i18n", "", $col_name) . " as \$lang=>\$datas){", 3);
                    $c .= StringUtils::println("\$ti18n = new " . $this->i18n_translate_class . "();", 4);
                    $c .= StringUtils::println("\$ti18n->language_slug = \$lang;", 4);
                    $c .= StringUtils::println("\$ti18n->i18n_id_i18n = " . $col->getAsVar("this") . ";", 4);
                    $c .= StringUtils::println("\$ti18n->datas=\$datas;", 4);
                    $c .= StringUtils::println("\$ti18n->create();", 4);
                    $c .= StringUtils::println("}", 3);
                    $c .= StringUtils::println("}", 2);
                }
            }
            $c .= StringUtils::println("if(\$force){", 2);
            $c .= StringUtils::println("$sql = \"INSERT INTO `$name` (" . implode(",", $table->getColumns("sql_name")) . ") VALUES (" . implode(",", $table->getColumns("sql_param")) . ")\";", 3);
            $c .= StringUtils::println("$prepare;", 3);
            $c .= StringUtils::println("\$bind_array = array(" . implode(",", $table->getColumns("bind_this")) . ");", 3);
            $c .= StringUtils::println("}else{", 2);
            $c .= StringUtils::println("$sql = \"INSERT INTO `$name` (" . implode(",", $table->getNotAutoIncColumns("sql_name")) . ") VALUES (" . implode(",", $table->getNotAutoIncColumns("sql_param")) . ")\";", 3);
            $c .= StringUtils::println("$prepare;", 3);
            $c .= StringUtils::println("\$bind_array = array(" . implode(",", $table->getNotAutoIncColumns("bind_this")) . ");", 3);
            $c .= StringUtils::println("}", 2);
            $c .= StringUtils::println("if(" . sprintf($execute, "\$bind_array") . " === false){", 2);
            $c .= StringUtils::println("Log::getHandler()->error(\$result->errorInfo());", 3);
            $c .= StringUtils::println("return false;", 3);
            $c .= StringUtils::println("}else{", 2);
            foreach ($table->columns as $col_name => $col) {
                if ($col->auto > 0) {
                    $c .= StringUtils::println("\$this->$col_name = (" . $col->type_php . ") Database::getHandler()->lastInsertId();", 3);
                    $c .= StringUtils::println("\$this->generateResourceURI();", 3);
                    break;
                }
            }
            if (isset($table->columns["password"])) {
                $c .= StringUtils::println("\$this->password=Encryption::password(\$this->password);", 3);
            }

            $c .= StringUtils::println("return true;", 3);
            $c .= StringUtils::println("}", 2);
            $c .= StringUtils::println("}", 1);


            $c .= StringUtils::println("public function delete() {", 1);
            if (isset($table->columns["trash"])) {
                $c .= StringUtils::println("\$this->trash=true;", 2);
                $c .= StringUtils::println("return \$this->update();", 2);
            } else {
                $c .= StringUtils::println("$sql = \"DELETE FROM `$name` WHERE " . implode(' AND ', $table->getPrimaryColumns("sql_cond")) . "\";", 2);
                $c .= StringUtils::println("$prepare;", 2);

                $c .= StringUtils::println(sprintf($execute, "array(" . implode(",", $table->getPrimaryColumns("bind_this")) . ")") . ";", 2);
            }
            $c .= StringUtils::println("}", 1);


            $c .= StringUtils::println("public function update(){", 1);
            if (isset($table->columns["slug"]) && isset($table->columns["name"])) {
                $c .= StringUtils::println("\$this->slug=StringUtils::slugify(\$this->name);", 2);
            }

            $c .= StringUtils::println("$sql = \"UPDATE `$name` SET " . implode(",", $table->getNotPrimaryColumns("sql_cond", true)) . " WHERE " . implode(" AND ", $table->getPrimaryColumns("sql_cond")) . "\";", 2);
            $c .= StringUtils::println("$prepare;", 2);

            foreach ($table->columns as $col_name => $col) {
                if (StringUtils::endsWith($col_name, "_i18n") && $col->auto == 0 && !StringUtils::startsWith($table->name, "i18n")) {
                    //TODO Rajouter les conditions de test pour la creation d'une trad
                    $c .= StringUtils::println("foreach(\$this->" . str_replace("_i18n", "", $col_name) . " as \$lang=>\$datas){", 2);
                    $c .= StringUtils::println("\$ti18n = Service" . $this->i18n_translate_class . "::getTranslation(" . $col->getAsVar("this") . ",\$lang);", 3);
                    $c .= StringUtils::println("\$ti18n->datas=\$datas;", 3);
                    $c .= StringUtils::println("\$ti18n->update();", 3);
                    $c .= StringUtils::println("}", 2);
                }
            }

            $c .= StringUtils::println("return " . sprintf($execute, "array(" . implode(",", $table->getColumns("bind_this", true))) . ");", 2);
            $c .= StringUtils::println("}", 1);
            if (isset($columns["name"])) {
                $c .= StringUtils::println("public function __toString(){", 1);
                $c .= StringUtils::println("return \$this->name;", 2);
                $c .= StringUtils::println("}", 1);
            }

            foreach ($table->columns as $col_name => $col) {
                if ($col->auto > 0) {
                    $c .= StringUtils::println("public function getId(){", 1);
                    $c .= StringUtils::println("return \$this->$col_name;", 2);
                    $c .= StringUtils::println("}", 1);
                    break;
                }
            }

            foreach ($table->onetomany as $k => $ref_tables) {
                $c .= StringUtils::println("public function get" . str_replace(" ", "", ucwords(str_replace("_", " ", $ref_tables["from"]))) . "(\$use_cache = false){", 1);
                $sql_cols = array();
                $sql_bind = array();
                $cache_cols = array();
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
                $c .= StringUtils::println("\$ret = array();", 3);
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

            foreach ($table->manytomany as $k => $ref_tables) {
                $c .= StringUtils::println("public function get" . str_replace(" ", "", ucwords(str_replace("_", " ", $ref_tables["from"]))) . "(\$use_cache = false){", 1);
                $sql_cols = array();
                $sql_bind = array();
                foreach ($ref_tables["ref"] as $cols) {
                    $ref_cols = explode("#", $cols);
                    $col_from = new Column();
                    $col_from->name = $ref_cols[0];

                    $col_to = new Column();
                    $col_to->name = $ref_cols[1];

                    $sql_cols[] = $col_from->getAsVar("sql_cond");
                    $sql_bind[] = '"' . $col_from->getAsVar("sql_param") . '"' . "=>" . $col_to->getAsVar("this");
                }

                $c .= StringUtils::println("\$c_key = \"key_" . $ref_tables["to"] . "_ref_" . $ref_tables["from"] . "\";", 2);
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
                $c .= StringUtils::println("\$ret = array();", 3);
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
                $c .= StringUtils::println("public function addRef(" . StringUtils::camelCase($ref_tables["from"]) . " \$" . StringUtils::camelCase($ref_tables["from"]) . "){", 1);
                $c .= StringUtils::println("}", 1);

                $c .= StringUtils::println("public function deleteRef(" . StringUtils::camelCase($ref_tables["from"]) . " \$" . StringUtils::camelCase($ref_tables["from"]) . "){", 1);
                $c .= StringUtils::println("}", 1);
            }
            /*
              foreach ($table->columns as $col_name => $col) {
              if ($col->auto == 0 && !in_array($col_name, array("trash"))) {
              $c .= StringUtils::println("public function get" . StringUtils::camelCase($col_name) . "() :" . $col->type_php . "{", 1);
              $c .= StringUtils::println("return " . $col->getAsVar("this") . ";", 2);
              $c .= StringUtils::println("}", 1);

              $c .= StringUtils::println("public function set" . StringUtils::camelCase($col_name) . "(" . $col->type_php . " \$" . $col->name . ") {", 1);
              $c .= StringUtils::println($col->getAsVar("this") . "= \$" . $col->name . ";", 2);
              $c .= StringUtils::println("}", 1);
              }
              }
             */
            $c .= StringUtils::printLn("}");
            fwrite($file, $c);
        }
    }

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

    private function generateImp() {
        $tables = $this->generateArrayTables();
        foreach ($tables as $name => $table) {
            if (strpos($name, "_has_") !== false) {
                continue;
            }
            $class = StringUtils::camelCase($name);

            $filename = $this->pathImp . "/" . $class . "Imp.php";
            if (file_exists($filename)) {
                unlink($filename);
            }
            $file = fopen($filename, "w+");
            $trash = false;
            if (isset($table->columns['trash'])) {
                $trash = true;
            }

            $c = StringUtils::printLn("<?php");
            $c .= StringUtils::println("namespace $this->namespaceImp;");
            foreach ($this->baseRequire as $require) {
                $c .= "use " . StringUtils::printLn($require . ";");
            }

            $c .= "use " . StringUtils::println($this->namespaceService . "\\Service" . StringUtils::camelCase($this->i18n_translate_class) . ";");

            if (StringUtils::camelCase($class) != StringUtils::camelCase($this->i18n_translate_class)) {
                $c .= "use " . StringUtils::println($this->namespaceClass . "\\" . StringUtils::camelCase($this->i18n_translate_class) . ";");
            }
            if (StringUtils::camelCase($class) != StringUtils::camelCase($this->i18n_class)) {
                $c .= "use " . StringUtils::println($this->namespaceClass . "\\" . StringUtils::camelCase($this->i18n_class) . ";");
            }
            if (StringUtils::camelCase($class) != StringUtils::camelCase($this->picture_class)) {
                $c .= "use " . StringUtils::println($this->namespaceClass . "\\" . StringUtils::camelCase($this->picture_class) . ";");
            }
            if (StringUtils::camelCase($class) != StringUtils::camelCase($this->language_class)) {
                $c .= "use " . StringUtils::println($this->namespaceClass . "\\" . StringUtils::camelCase($this->language_class) . ";");
            }
            $c .= "use " . StringUtils::println($this->namespaceClass . "\\" . StringUtils::camelCase($class) . ";");

            $c .= StringUtils::printLn("class " . $class . "Imp extends " . $class . " {");
            $c .= StringUtils::printLn("public \$resource_name = \"" . $class . "\";", 1);
            $c .= StringUtils::printLn("public \$resource_uri = null;", 1);
            $c .= StringUtils::printLn("public \$resource_namespace = __NAMESPACE__;", 1);
            foreach ($table->columns as $col_name => $col) {
                if (StringUtils::endsWith($col_name, "i18n") && $col->auto == 0 && !StringUtils::startsWith($table->name, "i18n")) {
                    $c .= StringUtils::println("public \$" . str_replace("_i18n", "", $col_name) . "=array();", 1);
                }
                if (StringUtils::endsWith($col_name, "_id_picture")) {
                    $c .= StringUtils::println("public \$" . str_replace("_id_picture", "", $col_name) . "=null;", 1);
                }
                $c .= StringUtils::println("public \$$col_name" . (isset($col->default) ? "=" . (is_numeric($col->default) ? $col->default : "\"" . $col->default . "\"") : "") . ";", 1);
            }

            $c .= StringUtils::println();
            $c .= StringUtils::println("public function __construct(){", 1);
            $c .= StringUtils::println("}", 1);
            $c .= StringUtils::println();
            foreach ($table->columns as $col_name => $col) {
                if ($col->auto == 0 && !in_array($col_name, array("trash"))) {
                    $c .= StringUtils::println("public function get" . StringUtils::camelCase($col_name) . "() :" . $col->type_php . "{", 1);
                    $c .= StringUtils::println("return " . $col->getAsVar("this") . ";", 2);
                    $c .= StringUtils::println("}", 1);

                    $c .= StringUtils::println("public function set" . StringUtils::camelCase($col_name) . "(" . $col->type_php . " \$" . $col->name . ") {", 1);
                    $c .= StringUtils::println($col->getAsVar("this") . "= \$" . $col->name . ";", 2);
                    $c .= StringUtils::println("}", 1);
                }
            }

            $c .= StringUtils::printLn("}");
            fwrite($file, $c);
        }
    }

    private function generateController() {
        $tables = $this->generateArrayTables();
        foreach ($tables as $name => $table) {
            if (strpos($name, "_has_") !== false) {
                continue;
            }
            $class = StringUtils::camelCase($name);

            $filename = $this->pathImp . "/../Controllers/Base/" . $class . "BaseController.php";
            if (file_exists($filename)) {
                unlink($filename);
            }
            $file = fopen($filename, "w+");
            $trash = false;
            if (isset($table->columns['trash'])) {
                $trash = true;
            }

            $c = StringUtils::printLn("<?php");
            $c .= StringUtils::println("namespace RadImp\Controllers\Base;");
            foreach ($this->baseRequire as $require) {
                $c .= "use " . StringUtils::printLn($require . ";");
            }
            $c .= StringUtils::println("use Rad\Http\Request;");
            $c .= StringUtils::println("use Rad\Http\Response;");
            $c .= StringUtils::println("use Rad\Route\Route;");
            $c .= "use " . StringUtils::println($this->namespaceService . "\\Service" . StringUtils::camelCase($class) . ";");
            $c .= "use " . StringUtils::println($this->namespaceClass . "\\" . StringUtils::camelCase($class) . ";");
            $c .= "use " . StringUtils::println("\\Rad\\Controller\\Controller;");
            $c .= StringUtils::printLn("class " . $class . "BaseController extends Controller {");
            $c .= StringUtils::println();
            $c .= StringUtils::println("public function __construct(){", 1);
            $c .= StringUtils::println("}", 1);
            $c .= StringUtils::println();
            $c .= StringUtils::println("/**", 1);
            $c .= StringUtils::println(" * @api 1", 1);
            $c .= StringUtils::println(" * @get /^" . $table->name . "$/", 1);
            $c .= StringUtils::println(" * @produce json", 1);
            $c .= StringUtils::println(" */", 1);
            $c .= StringUtils::println("public function getAll" . $class . "(Request \$request,Response \$response,Route \$route){", 1);
            $c .= StringUtils::println("return Service" . $class . "::getAll" . $class . '($request->offset, $request->limit, $request->isCache(), $request->get_datas);', 2);
            $c .= StringUtils::println("}", 1);

            $c .= StringUtils::println("/**", 1);
            $c .= StringUtils::println(" * @api 1", 1);
            $c .= StringUtils::println(" * @get /^" . $table->name . "\/([0-9]*)$/", 1);
            $c .= StringUtils::println(" * @produce json", 1);
            $c .= StringUtils::println(" */", 1);
            $c .= StringUtils::println("public function get" . $class . "(Request \$request,Response \$response,Route \$route){", 1);
            $c .= StringUtils::println("return Service" . $class . "::get" . $class . "(\$route->getArgs()[0],\$request->isCache());");
            $c .= StringUtils::println("}", 1);


            $c .= StringUtils::println("/**", 1);
            $c .= StringUtils::println(" * @api 1", 1);
            $c .= StringUtils::println(" * @post /^" . $table->name . "\/([0-9]*)$/", 1);
            $c .= StringUtils::println(" * @produce json", 1);
            $c .= StringUtils::println(" */", 1);
            $c .= StringUtils::println("public function post" . $class . "(Request \$request,Response \$response,Route \$route){", 1);
            $c .= StringUtils::println("}", 1);

            $c .= StringUtils::println("/**", 1);
            $c .= StringUtils::println(" * @api 1", 1);
            $c .= StringUtils::println(" * @put /^" . $table->name . "$/", 1);
            $c .= StringUtils::println(" * @produce json", 1);
            $c .= StringUtils::println(" */", 1);
            $c .= StringUtils::println("public function put" . $class . "(Request \$request,Response \$response,Route \$route){", 1);
            $c .= StringUtils::println("}", 1);

            $c .= StringUtils::println("/**", 1);
            $c .= StringUtils::println(" * @api 1");
            $c .= StringUtils::println(" * @patch /^" . $table->name . "\/([0-9]*)$/", 1);
            $c .= StringUtils::println(" * @produce json", 1);
            $c .= StringUtils::println(" */", 1);
            $c .= StringUtils::println("public function patch" . $class . "(Request \$request,Response \$response,Route \$route){", 1);
            $c .= StringUtils::println("}", 1);

            $c .= StringUtils::println("/**", 1);
            $c .= StringUtils::println(" * @api 1", 1);
            $c .= StringUtils::println(" * @options /^" . $table->name . "\/([0-9]*)$/", 1);
            $c .= StringUtils::println(" * @produce json", 1);
            $c .= StringUtils::println(" */", 1);
            $c .= StringUtils::println("public function options" . $class . "(Request \$request,Response \$response,Route \$route){", 1);
            $c .= StringUtils::println("}", 1);



            $c .= StringUtils::printLn("}");
            fwrite($file, $c);
        }
    }

    public function getTableIndexes($table, $columns) {
        $indexes = array();
        $sql = "SHOW KEYS FROM `$table` WHERE 1";
        $rows = Database::getHandler()->query($sql);
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
        return $indexes;
    }

    public function getTableStructure($table) {
        $columns = array();
        $result = Database::getHandler()->query("SHOW FULL COLUMNS FROM `$table`;");
        while ($row = $result->fetch()) {
            $column = new Column();
            $column->name = $row["Field"];

            if (strstr($row["Type"], "char") !== false || strstr($row["Type"], "text") !== false) {
                $column->type_sql = "\\PDO::PARAM_STR";
                $column->type_php = "string";
            } else if (strstr($row["Type"], "tinyint") !== false) {
                $column->type_sql = "\\PDO::PARAM_INT";
                $column->type_php = "boolean";
            } else if (strstr($row["Type"], "blob") !== false) {
                $column->type_sql = "\\PDO::PARAM_LOB";
                $column->type_php = "binary";
            } else if (strstr($row["Type"], "int") !== false) {
                $column->type_sql = "\\PDO::PARAM_INT";
                $column->type_php = "int";
            } else if (strstr("float", $row["Type"]) !== false || strstr("long", $row["Type"]) !== false || strstr("double", $row["Type"]) !== false) {
                $column->type_sql = "\\PDO::PARAM_STR";
                $column->type_php = "decimal";
            } else {
                $column->type_sql = "\\PDO::PARAM_STR";
                $column->type_php = "string";
            }
            $column->key = $row["Key"];

            $column->default = isset($row["Default"]) ? $row["Default"] : null;
            if (isset($row["Extra"]) && $row["Extra"] == "auto_increment") {
                $column->auto = 1;
            }
            $columns[$column->name] = $column;
        }
        return $columns;
    }

    /**
     * $ks = $this->search_keys($indexes, $col_name);
      foreach ($ks as $t) {
      if (isset($keys[$col_name])) {
      $indexes[$t[0]][$col_name] = $keys[$col_name];
      } else {
      $indexes[$t[0]][$col_name] = $columns[$col_name];
      }
      }
     */
    public function getManyToManyTable($table) {
        $tables = array();
        $sql = "SHOW TABLES FROM bb WHERE Tables_in_bb LIKE '" . $table . "_has_%'";
        $link_tables = Database::getHandler()->query($sql);
        while ($link_table = $link_tables->fetch(PDO::FETCH_NUM)) {
            $ext = str_replace($table . "_has_", "", $link_table[0]);
            $tables[] = array("from" => $table, "by" => $link_table[0], "to" => $ext);
        }
        return $tables;
    }

    public function getOneToOneTable($table) {
        
    }

    public function getOneToManyTable($table) {
        $tables = array();
        $fk_tables = "SELECT TABLE_NAME,GROUP_CONCAT(CONCAT(COLUMN_NAME,'#',REFERENCED_COLUMN_NAME) separator ',') AS cols
FROM
  information_schema.KEY_COLUMN_USAGE
WHERE
  REFERENCED_TABLE_NAME = '$table'
      AND TABLE_NAME NOT LIKE \"%_has_%\"
  AND TABLE_SCHEMA = 'bb' GROUP BY TABLE_NAME;";
        $link_tables = Database::getHandler()->query($fk_tables);
        while ($link_table = $link_tables->fetch()) {
            $tables[$link_table["TABLE_NAME"]] = array(
                "from" => $link_table["TABLE_NAME"],
                "ref" => explode(",", $link_table["cols"]),
                "to" => $table
            );
        }
        return $tables;
    }

    public function generateBindArray($keys, $k_type, $columns = null, $c_type = null) {
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

    public function generateBindArrayValue($keys, $k_type, $columns = null, $c_type = null) {
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

    public function search_keys($ar, $sr) {
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
