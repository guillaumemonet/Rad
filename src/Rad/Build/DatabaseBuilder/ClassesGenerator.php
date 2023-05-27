<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Build\DatabaseBuilder;

use Nette\PhpGenerator\ClassType;
use Rad\Build\DatabaseBuilder\Elements\Column;
use Rad\Build\DatabaseBuilder\Elements\Table;
use Rad\Utils\StringUtils;

/**
 * Description of ClassesGenerator
 *
 * @author Guillaume Monet
 */
class ClassesGenerator extends BaseGenerator {

    use ClassesGeneratorStaticTrait;

    public ?string $namespace   = null;
    public ?string $path        = null;
    public array $baseRequire = [
        "PDO",
        "Rad\\Model\\Model",
        "Rad\\Database\\Database",
        "Rad\\Cache\\Cache",
        "Rad\\Log\\Log",
        "Rad\\Utils\\StringUtils",
        "Rad\\Encryption\\Encryption"
    ];

    public function generateConstruct(ClassType $class, Table $table) {
        $parse = $class->addMethod('__construct');
        $parse->setVisibility('public');
        foreach ($table->getPrimaryColumns("name") as $name) {
            $parse->addParameter($name)->setDefaultValue(null);
        }
        foreach ($table->getPrimaryColumns("name") as $name) {
            $parse->addBody('$this->' . $name . ' = $' . $name . ';');
        }
    }

    public function generateCreate(ClassType $class, Table $table) {
        $parse = $class->addMethod('create');
        $parse->setVisibility('public');

        $parse->addParameter("force")->setDefaultValue(false)->setType("bool");

        if (isset($table->columns["slug"]) && isset($table->columns["name"])) {
            $parse->addBody("if(\$this->slug == null){");
            $parse->addBody("\$this->slug=StringUtils::slugify(\$this->name);");
            $parse->addBody("}");
        }
        foreach ($table->columns as $col_name => $col) {
            if (str_ends_with($col_name, "_i18n") && $col->auto == 0 && !str_starts_with($table->name, "i18n")) {
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
                $parse->addBody("\$this->generateResource();");
                break;
            }
        }
        if (isset($table->columns["password"])) {
            $parse->addBody("\$this->password=Encryption::password(\$this->password);");
        }

        $parse->addBody("}");
    }

    public function generateRead(ClassType $class, Table $table) {

        $parse = $class->addMethod('read');
        $parse->setVisibility('public');
        $parse->addBody("\$c_key = \$this->getCacheName();");
        $parse->addBody("\$row = unserialize(Cache::getHandler()->get(\$c_key));");
        $parse->addBody("if(\$row === false){");
        $parse->addBody("\$sql = \"SELECT * FROM `$table->name` WHERE " . implode(' AND ', $table->getPrimaryColumns("sql_cond")) . (isset($table->columns["trash"]) ? " AND `trash`=0 " : "") . "\";");
        $parse->addBody("$this->prepare;");
        $parse->addBody(sprintf($this->execute, "array(" . implode(",", $table->getPrimaryColumns("bind_this"))) . ")" . ";");
        $parse->addBody("\$row = \$result->fetch(\PDO::FETCH_ASSOC);");
        $parse->addBody('Cache::getHandler()->set($c_key,serialize($row));');
        $parse->addBody("}");
        $parse->addBody("if(is_array(\$row) && count(\$row) > 0){");
        $parse->addBody("\$this->parse(\$row);");
        $parse->addBody("}");
        $parse->addBody("\$this->generateResource();");
    }

    public function generateUpdate(ClassType $class, Table $table) {
        $parse = $class->addMethod('update');
        $parse->setVisibility('public');
        if (isset($table->columns["slug"]) && isset($table->columns["name"])) {
            $parse->addBody("\$this->slug=StringUtils::slugify(\$this->name);");
        }

        $parse->addBody("\$sql = \"UPDATE `$table->name` SET " . implode(",", $table->getNotPrimaryColumns("sql_cond", true)) . " WHERE " . implode(" AND ", $table->getPrimaryColumns("sql_cond")) . "\";");
        $parse->addBody("$this->prepare;");

        foreach ($table->columns as $col_name => $col) {
            if (str_ends_with($col_name, "_i18n") && $col->auto == 0 && !str_starts_with($table->name, "i18n")) {
                //TODO Rajouter les conditions de test pour la creation d'une trad
                $parse->addBody("foreach(\$this->" . str_replace("_i18n", "", $col_name) . " as \$lang=>\$datas){");
                $parse->addBody("\$ti18n = I18nTranslate::getTranslation(" . $col->getAsVar("this") . ",\$lang);");
                $parse->addBody("\$ti18n->datas=\$datas;");
                $parse->addBody("\$ti18n->update();");
                $parse->addBody("}");
            }
        }
        $parse->addBody('Cache::getHandler()->delete($this->getCacheName());');
        $parse->addBody("return " . sprintf($this->execute, "array(" . implode(",", $table->getColumns("bind_this", true))) . ");");
    }

    public function generateDelete(ClassType $class, Table $table) {
        $parse = $class->addMethod('delete');
        $parse->setVisibility('public');
        $parse->addBody('Cache::getHandler()->delete($this->getCacheName());');
        if (isset($table->columns["trash"])) {
            $parse->addBody("\$this->trash=true;");
            $parse->addBody("return \$this->update();");
        } else {
            $parse->addBody("\$sql = \"DELETE FROM `$table->name` WHERE " . implode(' AND ', $table->getPrimaryColumns("sql_cond")) . "\";");
            $parse->addBody("$this->prepare;");
            $parse->addBody(sprintf($this->execute, "array(" . implode(",", $table->getPrimaryColumns("bind_this")) . ")") . ";");
        }
    }

    public function generateCacheName(ClassType $class, Table $table) {
        $parse = $class->addMethod('getCacheName')->setReturnType('string');
        $parse->setVisibility('public');
        $parse->addBody("return \"key_" . strtolower($class->getName()) . "_\"." . implode('."_".', $table->getPrimaryColumns('this')) . ";");
    }

    public function generateProperties(ClassType $class, Table $table) {
        foreach ($table->columns as $col_name => $col) {
            if (str_ends_with($col_name, "i18n") && $col->auto == 0 && !str_starts_with($table->name, "i18n")) {
                $propertyName = str_replace("_i18n", "", $col_name);
                $property     = $class->addProperty($propertyName)
                        ->setValue([])
                        ->setVisibility('public');
            }
            if (str_ends_with($col_name, "_id_picture")) {
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
        $tab      = [];
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
        $parse = $class->addMethod('parse');
        $parse->setVisibility('public');
        $parse->addParameter("row")->setType("array");
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
            if (str_ends_with($col_name, "_i18n") && $col->auto == 0 && !str_starts_with($table->name, "i18n")) {
                $parse->addBody("if(\$this->" . str_replace("_i18n", "", $col_name) . " == null){");
                $parse->addBody("\$ti18ns = I18nTranslate::getTranslationFromId(" . $col->getAsVar("this") . ",null,null);");
                $parse->addBody("foreach(\$ti18ns as \$i18n){");
                $parse->addBody("\$this->" . str_replace("_i18n", "", $col_name) . "[\$i18n->language_slug] = \$i18n->datas;");
                $parse->addBody("}");
                $parse->addBody("}");
            }
            if (str_ends_with($col_name, "_id_picture")) {
                $parse->addBody("if(\$this->" . $col_name . " > 0 && \$this->" . str_replace("_id_picture", "", $col_name) . " == null){");
                $parse->addBody("\$this->" . str_replace("_id_picture", "", $col_name) . " = Picture::getPicture(" . $col->getAsVar("this") . ");");
                $parse->addBody("}");
            }
        }
    }

    public function generateOneToManys(ClassType $class, Table $table) {

        foreach ($table->onetomany as $k => $ref_tables) {
            $parse      = $class->addMethod('getAll' . StringUtils::camelCase($k));
            $parse->setVisibility('public');
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

}
