<?php

namespace Rad\bin\scripts\templates;

use Rad\bin\scripts\CommonClassTrait;
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
 * Description of DAOTemplate
 *
 * @author guillaume
 */
class DAOTemplate {

    use DAOTemplateCreateTrait;
    use DAOTemplateReadTrait;
    use DAOTemplateUpdateTrait;
    use DAOTemplateDeleteTrait;
    use DAOTemplateParseTrait;
    use DAOOneToManyTrait;
    use DAOManyToManyTrait;
    use DAOTemplateTraitTrait;
    use RequestTrait;
    use CommonClassTrait;

    public $namespace = null;
    private $tableName = null;
    private $className = null;
    private $tableStructure = null;
    private $useclasses = array(
        'PDO',
        'Rad\\Model\\Model',
        'Rad\\Model\\ModelDAO',
        'Rad\\Database\\Database',
        'Rad\\Cache\\Cache',
        'Rad\\Log\\Log',
        'Rad\\Utils\\StringUtils',
        'Rad\\Encryption\\Encryption'
    );
    private $trash = false;

    public function __construct($className, $namespace, $tableName, $tableStructure) {
        $this->tableName = $tableName;
        $this->className = $className;
        $this->tableStructure = $tableStructure;
        $this->namespace = $namespace;
        $this->trash = false;
        if (isset($this->tableStructure->columns['trash'])) {
            $this->trash = true;
        }
        $this->generateClassName();
        $this->generateOneToManyUse();
        $this->generateManyToManyUse();
    }

    private function generateOneToManyUse() {
        foreach ($this->tableStructure->onetomany as $ref_table) {
            if ($ref_table["from"] != $this->tableName) {
                $this->addUseClasse(StringUtils::camelCase($ref_table["from"]));
            }
        }
    }

    private function generateManyToManyUse() {
        foreach ($this->tableStructure->manytomany as $linked_table) {
            if ($linked_table["to"] != $this->tableName) {
                $this->addUseClasse(StringUtils::camelCase($linked_table["to"]));
            }
        }
    }

    public function printNamespace() {
        return StringUtils::println('namespace ' . $this->namespace . ';');
    }

    public function printUseClasses() {
        return array_reduce($this->useclasses, function($carry, $item) {
            return $carry . StringUtils::println('use ' . $item . ';');
        });
    }

    public function printStartTrait() {
        $c = StringUtils::println('trait ' . $this->className . 'Trait {');
        return $c;
    }

    public function printStartClass() {
        $c = StringUtils::println('class ' . $this->className . ' extends Model {');
        $c .= StringUtils::println('use ' . $this->className . 'Trait;', 1);
        return $c;
    }

    public function printEndClass() {
        return '}';
    }

    public function printContructor() {
        $c = StringUtils::println('public function __construct(){', 1);
        $c .= StringUtils::println("}", 1);
        return $c;
    }

    public function addUseClasse(string $classname) {
        $this->useclasses[$classname] = $classname;
        return $this;
    }

    public function printToString() {
        if (isset($this->tableStructure->columns['name'])) {
            $c = StringUtils::println('public function __toString(){', 1);
            $c .= StringUtils::println('return $this->name;', 2);
            $c .= StringUtils::println('}', 1);
            return $c;
        } else {
            return null;
        }
    }

    public function printGetId() {
        foreach ($this->tableStructure->columns as $col_name => $col) {
            if ($col->auto > 0) {
                $c = StringUtils::println('public function getId(){', 1);
                $c .= StringUtils::println('return \$this->' . $col_name . ';', 2);
                $c .= StringUtils::println('}', 1);
                return $c;
            }
        }
        return null;
    }

    public function printAttributes() {
        $c = StringUtils::printLn("public \$resource_name = \"" . $this->className . "\";", 1);
        $c .= StringUtils::printLn("public \$resource_uri = null;", 1);
        $c .= StringUtils::printLn("public \$resource_namespace = __NAMESPACE__;", 1);
        foreach ($this->tableStructure->columns as $col_name => $col) {
            if (StringUtils::endsWith($col_name, "i18n") && $col->auto == 0 && !StringUtils::startsWith($this->tableStructure->name, "i18n")) {
                $c .= StringUtils::println("public \$" . str_replace("_i18n", "", $col_name) . "=array();", 1);
            }
            if (StringUtils::endsWith($col_name, "_id_picture")) {
                $c .= StringUtils::println("public \$" . str_replace("_id_picture", "", $col_name) . "=null;", 1);
            }
            $c .= StringUtils::println("public \$$col_name" . (isset($col->default) ? "=" . (is_numeric($col->default) ? $col->default : "\"" . $col->default . "\"") : "") . ";", 1);
        }

        $c .= StringUtils::println("private static \$tableFormat =array(", 1);
        foreach ($this->tableStructure->columns as $col_name => $col) {
            $c .= StringUtils::println("\"" . $col_name . "\"=>" . $col->type_sql . ",", 2);
        }
        $c .= StringUtils::println(");", 1);
        return $c;
    }

}
