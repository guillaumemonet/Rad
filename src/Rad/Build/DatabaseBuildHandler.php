<?php

namespace Rad\Build;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use PDO;
use Rad\Build\Elements\Column;
use Rad\Build\Elements\Index;
use Rad\Build\Elements\Table;
use Rad\Build\Templates\DAOTemplate;
use Rad\Database\Database;
use Rad\Utils\StringUtils;

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

class DatabaseBuildHandler implements BuildInterface {

    protected $baseRequire = [
        "PDO",
        "Rad\\Model\\Model",
        "Rad\\Model\\ModelDAO",
        "Rad\\Database\\Database",
        "Rad\\Cache\\Cache",
        "Rad\\Log\\Log",
        "Rad\\Utils\\StringUtils",
        "Rad\\Encryption\\Encryption"
    ];
    private ?string $namespace     = null;
    private ?string $path          = null;
    protected string $query       = "\$result = Database::getHandler()->query(\$sql)";
    protected string $prepare     = "\$result = Database::getHandler()->prepare(\$sql)";
    protected string $execute     = "\$result->execute(%s)";
    protected string $result      = "\$res = \$result->fetchAll(\PDO::FETCH_ASSOC)";

    public function __construct() {
        
    }

    public function build($namespace = "Rad\\Datas", $path = null) {
        $this->namespace = $namespace;
        $this->path      = $path;
        $tables          = $this->generateArrayTables();
        $this->generateClass($tables);

        return "Generated";
    }

    private function generateClass(array $tables) {
        foreach ($tables as $name => $table) {
            if (strpos($name, "_has_") !== false) {
                continue;
            }
            $className = StringUtils::camelCase($name);

            $namespace = new PhpNamespace($this->namespace);
            foreach ($this->baseRequire as $n) {
                $namespace->addUse($n);
            }
            $class = $namespace->addClass($className);

            $this->generateProperties($class, $table);
            $this->generateTableFormat($class, $table);
            $this->generateCreate($class, $table);
            $this->generateRead($class, $table);
            $this->generateUpdate($class, $table);
            $this->generateDelete($class, $table);
            $this->generateGetId($class, $table);
            $this->generateParse($class, $table);
            $this->generateOneToManys($class, $table);
            $this->generateIndexesGetter($class, $table);

            $this->generateGetAll($class, $table);
            $filename = $this->path . $className . '.php';
            file_put_contents($filename, "<?php\n\n" . StringUtils::reindent((string) $namespace));
        }
    }

    public function generateProperties(ClassType $class, Table $table) {
        foreach ($table->columns as $col_name => $col) {
            if (StringUtils::endsWith($col_name, "i18n") && $col->auto == 0 && !StringUtils::startsWith($table->name, "i18n")) {
                $propertyName = str_replace("_i18n", "", $col_name);
                $property     = $class->addProperty($propertyName)
                        ->setValue([])
                        ->setVisibility('public');
            }
            if (StringUtils::endsWith($col_name, "_id_picture")) {
                $propertyName = str_replace("_id_picture", "", $col_name);
                $property     = $class->addProperty($propertyName)
                        ->setVisibility('public');
            }
            $propertyName = $col_name;
            $property     = $class->addProperty($propertyName)
                    ->setValue($col->default)
                    ->setType("?" . $col->type_php)
                    ->setVisibility('public');
        }
    }

    public function generateTableFormat(ClassType $class, Table $table) {
        $property = $class->addProperty('tableFormat')
                ->setType('array')
                ->setStatic();

        $tab = [];
        foreach ($table->columns as $col_name => $col) {
            $tab[$col_name] = $col->type_sql;
        }
        $property->setValue($tab);
    }

    public function generateGetId(ClassType $class, Table $table) {
        foreach ($table->columns as $col_name => $col) {
            if ($col->auto > 0) {
                $class->addMethod('getId')
                        ->setVisibility('public')
                        ->setBody('return $this->' . $col_name . ';');
            }
        }
    }

    public function generateParse(ClassType $class, Table $table) {
        $parse = $class->addMethod('parse')
                ->setVisibility('public')
                ->addParameter("row")
                ->setType("array");
        foreach ($table->columns as $col_name => $col) {
            $def = "null";
            if (isset($col->default)) {
                $def = var_export($col->default, true);
            }
            $parse->addBody("\$this->$col_name=isset(\$row['$col_name'])?\$row['$col_name']:" . $def . ";");
        }
        $parse->addBody("\$other_values = array_diff_key(\$row,self::\$tableFormat);");
        $parse->addBody("if(count(\$other_values) > 0){");
        $parse->addBody("foreach(\$other_values as \$k=>\$v){");
        $parse->addBody("if(is_array(\$this->\$k)){");
        $parse->addBody("\$this->\$k=(array) \$v;");
        $parse->addBody("}else{");
        $parse->addBody("\$this->\$k=\$v;");
        $parse->addBody("}");
        $parse->addBody("}");
        $parse->addBody("}");
        foreach ($table->columns as $col_name => $col) {
            if (StringUtils::endsWith($col_name, "_i18n") && $col->auto == 0 && !StringUtils::startsWith($table->name, "i18n")) {
                $parse->addBody("if(\$this->" . str_replace("_i18n", "", $col_name) . " == null){");
                $parse->addBody("\$ti18ns = I18nTranslate::getTranslationFromId(" . $col->getAsVar("this") . ",null,null);");
                $parse->addBody("foreach(\$ti18ns as \$i18n){");
                $parse->addBody("\$this->" . str_replace("_i18n", "", $col_name) . "[\$i18n->language_slug] = \$i18n->datas;");
                $parse->addBody("}");
                $parse->addBody("}");
            }
            if (StringUtils::endsWith($col_name, "_id_picture")) {
                $parse->addBody("if(\$this->" . $col_name . " > 0 && \$this->" . str_replace("_id_picture", "", $col_name) . " == null){");
                $parse->addBody("\$this->" . str_replace("_id_picture", "", $col_name) . " = Picture::getPicture(" . $col->getAsVar("this") . ");");
                $parse->addBody("}");
            }
        }
    }

    public function generateIndexesGetter(ClassType $class, Table $table) {
        foreach ($table->indexes as $k => $i) {
            if ($i->name == "PRIMARY") {
                $this->generatePrimaryIndexGetter($class, $k, $i);
            } else if ($i->unique) {
                $this->generateUniqueIndexGetter($class, $table, $k, $i);
            } else {
                $this->generateOtherIndexGetter($class, $table, $k, $i);
            }
        }
    }

    public function generatePrimaryIndexGetter(ClassType $class, $k, $i) {
        $parse = $class->addMethod('get' . $class->getName())
                ->setStatic()
                ->setVisibility('public');

        foreach ($i->getColumns("name") as $param) {
            $parse->addParameter($param);
        }
        $parse->addParameter("useCache")->setDefaultValue(false)->setType("bool");

        $parse->addBody('$c = new ' . $class->getName() . '();');
        $parse->addBody('$c->read(' . implode(",", $i->getColumns("php")) . ',$useCache);');
        $parse->addBody('return $c;');
    }

    public function generateUniqueIndexGetter(ClassType $class, Table $table, $k, $i) {
        $parse = $class->addMethod($k)
                ->setStatic()
                ->setVisibility('public');

        foreach ($i->getColumns("name") as $param) {
            $parse->addParameter($param);
        }
        $parse->addBody("\$c_key = \"cache_" . $k . "_\"." . implode('."_".', $i->getColumns("php")) . ";");
        $parse->addBody("\$" . $table->name . " = unserialize(Cache::getHandler()->get(\$c_key));");
        $parse->addBody("if(\$" . $table->name . " === false){");
        $parse->addBody("\$sql = \"SELECT * FROM " . $table->name . " WHERE " . implode(" AND ", $i->getColumns("sql_cond")) . (isset($table->columns["trash"]) ? " AND `trash`=0 " : "") . "\";");
        $parse->addBody("\$result = Database::getHandler()->prepare(\$sql);");
        $parse->addBody("\$result->execute(array(" . implode(",", $i->getColumns("bind")) . "));");
        $parse->addBody("\$row = \$result->fetch(\PDO::FETCH_ASSOC);");
        $parse->addBody("if(is_array(\$row) && count(\$row) > 0){");
        $parse->addBody("\$$table->name = new " . $class->getName() . "();");
        $parse->addBody("\$" . $table->name . "->parse(\$row);");
        $parse->addBody("Cache::getHandler()->set(\$c_key,serialize(\$" . $table->name . "));");
        $parse->addBody("}");
        $parse->addBody("}");
        $parse->addBody("return \$$table->name;");
    }

    public function generateOtherIndexGetter(ClassType $class, Table $table, $k, $i) {

        $parse = $class->addMethod($k)
                ->setStatic()
                ->setVisibility('public');

        foreach ($i->getColumns("name") as $param) {
            $parse->addParameter($param);
        }
        $parse->addParameter("offset")->setDefaultValue(null);
        $parse->addParameter("limit")->setDefaultValue(null);
        $parse->addBody("\$c_key = \"cache_" . $k . "_\"." . implode('."_".', $i->getColumns("php")) . ".\$limit.\"_\".\$offset;");
        $parse->addBody("\$ret = unserialize(Cache::getHandler()->get(\$c_key));");
        $parse->addBody("if(\$ret === false){");
        $parse->addBody("\$sql = \"SELECT * FROM " . $table->name . " WHERE " . implode(" AND ", $i->getColumns("sql_cond")) . (isset($table->columns["trash"]) ? " AND `trash`=0 " : "") . "\".(\$offset !==null && \$limit !== null?\" LIMIT \$offset,\$limit\":\"\").\"\";");
        $parse->addBody("\$result = Database::getHandler()->prepare(\$sql);");
        $parse->addBody("\$result->execute(array(" . implode(",", $i->getColumns("bind")) . "));");
        $parse->addBody("\$ret = [];");
        $parse->addBody("while(\$row = \$result->fetch(\PDO::FETCH_ASSOC)){");
        $parse->addBody("\$" . $table->name . " = new " . $class->getName() . "();");
        $parse->addBody("\$" . $table->name . "->parse(\$row);");
        $parse->addBody("\$ret[] = \$" . $table->name . ";");
        $parse->addBody("}");
        $parse->addBody("Cache::getHandler()->set(\$c_key,serialize(\$ret));");
        $parse->addBody("}");
        $parse->addBody("return \$ret;");
    }

    public function generateCreate(ClassType $class, Table $table) {
        $parse = $class->addMethod('create')
                ->setVisibility('public');

        $parse->addParameter("force")->setDefaultValue(false)->setType("bool");

        if (isset($table->columns["slug"]) && isset($table->columns["name"])) {
            $parse->addBody("if(\$this->slug == null){");
            $parse->addBody("\$this->slug=StringUtils::slugify(\$this->name);");
            $parse->addBody("}");
        }
        foreach ($table->columns as $col_name => $col) {
            if (StringUtils::endsWith($col_name, "_i18n") && $col->auto == 0 && !StringUtils::startsWith($table->name, "i18n")) {
                $parse->addBody("if(" . $col->getAsVar("this") . " == null){");
                $parse->addBody("\$li18n = new I18n();");
                $parse->addBody("\$li18n->name = \"" . $table->name . "_" . $col_name . "\";");
                $parse->addBody("\$li18n->table= \"" . $table->name . "\";");
                $parse->addBody("\$li18n->row= \"" . $col_name . "\";");
                $parse->addBody("\$li18n->create();");
                $parse->addBody($col->getAsVar("this") . " = \$li18n->getId();");
                $parse->addBody("foreach(\$this->" . str_replace("_i18n", "", $col_name) . " as \$lang=>\$datas){");
                $parse->addBody("\$ti18n = new I18nTranslate();");
                $parse->addBody("\$ti18n->language_slug = \$lang;");
                $parse->addBody("\$ti18n->i18n_id = " . $col->getAsVar("this") . ";");
                $parse->addBody("\$ti18n->datas=\$datas;");
                $parse->addBody("\$ti18n->create();");
                $parse->addBody("}");
                $parse->addBody("}");
            }
        }
        $parse->addBody("if(\$force){");
        $parse->addBody("\$sql = \"INSERT INTO `$table->name` (" . implode(",", $table->getColumns("sql_name")) . ") VALUES (" . implode(",", $table->getColumns("sql_param")) . ")\";");
        $parse->addBody("$this->prepare;");
        $parse->addBody("\$bind_array = array(" . implode(",", $table->getColumns("bind_this")) . ");");
        $parse->addBody("}else{");
        $parse->addBody("\$sql = \"INSERT INTO `$table->name` (" . implode(",", $table->getNotAutoIncColumns("sql_name")) . ") VALUES (" . implode(",", $table->getNotAutoIncColumns("sql_param")) . ")\";");
        $parse->addBody("$this->prepare;");
        $parse->addBody("\$bind_array = [" . implode(",", $table->getNotAutoIncColumns("bind_this")) . "];");
        $parse->addBody("}");
        $parse->addBody("if(" . sprintf($this->execute, "\$bind_array") . " === false){");
        $parse->addBody("Log::getHandler()->error(\$result->errorInfo());");
        $parse->addBody("return false;");
        $parse->addBody("}else{");
        foreach ($table->columns as $col_name => $col) {
            if ($col->auto > 0) {
                $parse->addBody("\$this->$col_name = (" . $col->type_php . ") Database::getHandler()->lastInsertId();");
                //$parse->addBody("\$this->generateResourceURI();");
                break;
            }
        }
        if (isset($table->columns["password"])) {
            $parse->addBody("\$this->password=Encryption::password(\$this->password);");
        }

        $parse->addBody("}");
    }

    public function generateRead(ClassType $class, Table $table) {

        $parse = $class->addMethod('read')
                ->setVisibility('public');
        foreach ($table->getPrimaryColumns("name") as $name) {
            $parse->addParameter($name);
        }
        $parse->addBody("\$c_key = \"key_" . strtolower($class->getName()) . "_\"." . implode('."_".', $table->getPrimaryColumns("php")) . ";");
        $parse->addBody("\$row = unserialize(Cache::getHandler()->get(\$c_key));");
        $parse->addBody("if(\$row === false){");
        $parse->addBody("\$sql = \"SELECT * FROM `$table->name` WHERE " . implode(' AND ', $table->getPrimaryColumns("sql_cond")) . (isset($table->columns["trash"]) ? " AND `trash`=0 " : "") . "\";");
        $parse->addBody("$this->prepare;");
        $parse->addBody(sprintf($this->execute, "array(" . implode(",", $table->getPrimaryColumns("bind"))) . ")" . ";");
        $parse->addBody("\$row = \$result->fetch(\PDO::FETCH_ASSOC);");
        $parse->addBody('Cache::getHandler()->set($c_key,serialize($row));');
        $parse->addBody("}");
        $parse->addBody("if(is_array(\$row) && count(\$row) > 0){");
        $parse->addBody("\$this->parse(\$row);");
        $parse->addBody("}");
    }

    public function generateUpdate(ClassType $class, Table $table) {
        $parse = $class->addMethod('update')
                ->setVisibility('public');
        if (isset($table->columns["slug"]) && isset($table->columns["name"])) {
            $parse->addBody("\$this->slug=StringUtils::slugify(\$this->name);");
        }

        $parse->addBody("\$sql = \"UPDATE `$table->name` SET " . implode(",", $table->getNotPrimaryColumns("sql_cond", true)) . " WHERE " . implode(" AND ", $table->getPrimaryColumns("sql_cond")) . "\";");
        $parse->addBody("$this->prepare;");

        foreach ($table->columns as $col_name => $col) {
            if (StringUtils::endsWith($col_name, "_i18n") && $col->auto == 0 && !StringUtils::startsWith($table->name, "i18n")) {
                //TODO Rajouter les conditions de test pour la creation d'une trad
                $parse->addBody("foreach(\$this->" . str_replace("_i18n", "", $col_name) . " as \$lang=>\$datas){");
                $parse->addBody("\$ti18n = I18nTranslate::getTranslation(" . $col->getAsVar("this") . ",\$lang);");
                $parse->addBody("\$ti18n->datas=\$datas;");
                $parse->addBody("\$ti18n->update();");
                $parse->addBody("}");
            }
        }

        $parse->addBody("return " . sprintf($this->execute, "array(" . implode(",", $table->getColumns("bind_this", true))) . ");");
    }

    public function generateDelete(ClassType $class, Table $table) {
        $parse = $class->addMethod('delete')
                ->setVisibility('public');
        if (isset($table->columns["trash"])) {
            $parse->addBody("\$this->trash=true;");
            $parse->addBody("return \$this->update();");
        } else {
            $parse->addBody("\$sql = \"DELETE FROM `$table->name` WHERE " . implode(' AND ', $table->getPrimaryColumns("sql_cond")) . "\";");
            $parse->addBody("$this->prepare;");
            $parse->addBody(sprintf($this->execute, "array(" . implode(",", $table->getPrimaryColumns("bind_this")) . ")") . ";");
        }
    }

    public function generateGetAll(ClassType $class, Table $table) {

        $parse = $class->addMethod('getAll')
                ->setStatic()
                ->setVisibility('public');
        $parse->addParameter('offset');
        $parse->addParameter('limit');
        $parse->addBody("\$c_key = \"key_" . $class->getName() . "_all_" . $table->name . "_\".\$limit.\"_\".\$offset;");
        $parse->addBody("\$ret = unserialize(Cache::getHandler()->get(\$c_key));");
        $parse->addBody("if(\$ret === false){");
        $parse->addBody("\$sql = \"SELECT * FROM " . $table->name . " WHERE 1 " . (isset($table->columns["trash"]) ? " AND trash=0 " : "") . " \".(\$offset !==null && \$limit !== null ?\" LIMIT \$offset,\$limit\":\"\").\"\";");
        $parse->addBody("\$result = Database::getHandler()->prepare(\$sql);");
        $parse->addBody("\$result->execute();");
        $parse->addBody("\$ret = [];");
        $parse->addBody("while(\$row = \$result->fetch(\PDO::FETCH_ASSOC)){");
        $parse->addBody("\$$table->name = new " . $class->getName() . ";");
        $parse->addBody("\$" . $table->name . "->parse(\$row);");
        $parse->addBody("\$ret[] = \$$table->name;");
        $parse->addBody("}");
        $parse->addBody("Cache::getHandler()->set(\$c_key,serialize(\$ret));");
        $parse->addBody("}");
        $parse->addBody("return \$ret;");
    }

    public function generateOneToManys(ClassType $class, Table $table) {

        foreach ($table->onetomany as $k => $ref_tables) {
            $parse      = $class->addMethod('getAll' . StringUtils::camelCase($k))
                    ->setVisibility('public');
            $parse->addParameter('offset');
            $parse->addParameter('limit');
            $sql_cols   = [];
            $sql_bind   = [];
            $cache_cols = [];
            foreach ($ref_tables["ref"] as $cols) {
                $ref_cols       = explode("#", $cols);
                $col_from       = new Column();
                $col_from->name = $ref_cols[0];

                $col_to       = new Column();
                $col_to->name = $ref_cols[1];

                $sql_cols[]   = $col_from->getAsVar("sql_cond");
                $sql_bind[]   = '"' . $col_from->getAsVar("sql_param") . '"' . "=>" . $col_to->getAsVar("this");
                $cache_cols[] = $col_to->getAsVar("this");
            }

            $parse->addBody("\$c_key = \"key_" . $ref_tables["to"] . "_ref_" . $ref_tables["from"] . "_\"." . implode('."_".', $cache_cols) . ";");
            $parse->addBody("\$ret = unserialize(Cache::getHandler()->get(\$c_key));");
            $parse->addBody("if(\$ret === false){");
            $parse->addBody("\$sql = \"SELECT * FROM " . $ref_tables["from"] . " WHERE " . implode(" AND ", $sql_cols) . " \".(\$offset !==null && \$limit !== null ?\" LIMIT \$offset,\$limit\":\"\").\"\";");
            $parse->addBody("\$result = Database::getHandler()->prepare(\$sql);");
            $parse->addBody("\$result->execute(array(" . implode(",", $sql_bind) . "));");
            $parse->addBody("\$ret = [];");
            $parse->addBody("while(\$row = \$result->fetch(\\PDO::FETCH_ASSOC)){");
            $parse->addBody("\$" . $ref_tables["from"] . " = new " . StringUtils::camelCase($ref_tables["from"]) . "();");
            $parse->addBody("\$" . $ref_tables["from"] . "->parse(\$row);");
            $parse->addBody("\$ret[] = $" . $ref_tables["from"] . ";");
            $parse->addBody("}");
            $parse->addBody("Cache::getHandler()->set(\$c_key,serialize(\$ret));");
            $parse->addBody("}");
            $parse->addBody("return \$ret;");
        }
    }

    private function generateArrayTables() {
        $sql        = "SHOW TABLES";
        $res_tables = Database::getHandler()->query($sql);

        $tables = [];
        while ($row    = $res_tables->fetch()) {
            $table                = new Table();
            $table->name          = $row[0];
            $table->columns       = $this->getTableStructure($table->name);
            $table->indexes       = $this->getTableIndexes($table->name, $table->columns);
            $table->onetomany     = $this->getOneToManyTable($table->name);
            $table->manytomany    = $this->getManyToManyTable($table->name);
            $tables[$table->name] = $table;
        }
        return $tables;
    }

    public function getTableIndexes($table, $columns) {
        $indexes = [];
        $sql     = "SHOW KEYS FROM `$table` WHERE 1";
        $rows    = Database::getHandler()->query($sql);
        while ($tkey    = $rows->fetch()) {
            if (!isset($indexes[$tkey["Key_name"]])) {
                $index                                = new Index();
                $index->name                          = $tkey["Key_name"];
                $index->unique                        = !$tkey["Non_unique"];
                $index->columns[$tkey["Column_name"]] = $columns[$tkey["Column_name"]];
                $indexes[$tkey["Key_name"]]           = $index;
            } else {
                $indexes[$tkey["Key_name"]]->columns[$tkey["Column_name"]] = $columns[$tkey["Column_name"]];
            }
        }
        return $indexes;
    }

    public function getTableStructure($table) {
        $columns = [];
        $result  = Database::getHandler()->query("SHOW FULL COLUMNS FROM `$table`;");
        while ($row     = $result->fetch()) {
            $column       = new Column();
            $column->name = $row["Field"];
            $this->setType($row, $column);
            $column->key  = $row["Key"];
            if (isset($row["Default"])) {
                settype($row["Default"], $column->type_php);
                $column->default = $row["Default"];
            }
            if (isset($row["Extra"]) && $row["Extra"] == "auto_increment") {
                $column->auto = 1;
            }
            $columns[$column->name] = $column;
        }
        return $columns;
    }

    private function setType($row, $column) {

        $type = strtolower($row["Type"]);

        $types = [
            "char"    => ["\\PDO::PARAM_STR", "string"],
            "text"    => ["\\PDO::PARAM_STR", "string"],
            "tinyint" => ["\\PDO::PARAM_INT", "bool"],
            "blob"    => ["\\PDO::PARAM_BLOB", "binary"],
            "int"     => ["\\PDO::PARAM_INT", "int"],
            "float"   => ["\\PDO::PARAM_STR", "float"],
            "long"    => ["\\PDO::PARAM_STR", "float"],
            "double"  => ["\\PDO::PARAM_STR", "float"]
        ];

        if (array_key_exists($type, $types)) {
            $column->type_sql = $types[$type][0];
            $column->type_php = $types[$type][1];
        } else {
            $column->type_sql = "\\PDO::PARAM_STR";
            $column->type_php = "string";
        }
    }

    public function getManyToManyTable($table) {
        $tables      = [];
        $sql         = "SHOW TABLES LIKE '" . $table . "_has_%'";
        $link_tables = Database::getHandler()->query($sql);
        while ($link_table  = $link_tables->fetch(PDO::FETCH_NUM)) {
            $ext      = str_replace($table . "_has_", "", $link_table[0]);
            $tables[] = array("from" => $table, "by" => $link_table[0], "to" => $ext);
        }
        return $tables;
    }

    public function getOneToManyTable($table) {
        $tables      = [];
        $fk_tables   = "SELECT CONSTRAINT_NAME ,TABLE_NAME,GROUP_CONCAT(CONCAT(COLUMN_NAME,'#',REFERENCED_COLUMN_NAME) separator ',') AS cols
FROM
  information_schema.KEY_COLUMN_USAGE
WHERE
  REFERENCED_TABLE_NAME = '$table'
      AND TABLE_NAME NOT LIKE \"%_has_%\"
  GROUP BY CONSTRAINT_NAME;";
        $link_tables = Database::getHandler()->query($fk_tables);
        while ($link_table  = $link_tables->fetch()) {
            $tables[$link_table["CONSTRAINT_NAME"]] = array(
                "from" => $link_table["TABLE_NAME"],
                "ref"  => explode(",", $link_table["cols"]),
                "to"   => $table
            );
        }
        return $tables;
    }

    public function printController(DAOTemplate $daomodel) {
        $fileController = $this->createFile($this->pathController . '/' . $daomodel->className . 'BaseController.php');
        $c              = StringUtils::println("<?php");
        $c              .= $daomodel->printControllerNamespace();
        $c              .= $daomodel->printControllerUseClasses();
        $c              .= $daomodel->printStartController();
        $c              .= $daomodel->printControllerGetAll();
        $c              .= $daomodel->printControllerGetOne();
        $c              .= $daomodel->printControllerPostOne();
        $c              .= $daomodel->printControllerDeleteOne();
        $c              .= $daomodel->printControllerPutOne();
        $c              .= $daomodel->printControllerPatchOne();
        $c              .= $daomodel->printEndClass();
        fwrite($fileController, $c);
    }

}
