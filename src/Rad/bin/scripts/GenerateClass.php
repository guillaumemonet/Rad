<?php

namespace Rad\bin\scripts;

use Rad\bin\scripts\elements\Column;
use Rad\Utils\StringUtils;

/**
 * Description of GenerateClass
 *
 * @author guillaume
 */
trait GenerateClass {

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

}
