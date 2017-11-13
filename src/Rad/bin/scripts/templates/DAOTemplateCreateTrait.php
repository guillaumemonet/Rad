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
 * Description of DAOTemplateCreateTrait
 *
 * @author guillaume
 */
trait DAOTemplateCreateTrait {

    public function printCreate() {
        $c = StringUtils::println("public function create(\$force=false){", 1);
        if (isset($this->tableStructure->columns["slug"]) && isset($this->tableStructure->columns["name"])) {
            $c .= StringUtils::println("if(\$this->slug == null){", 2);
            $c .= StringUtils::println("\$this->slug=StringUtils::slugify(\$this->name);", 3);
            $c .= StringUtils::println("}", 2);
        }
        foreach ($this->tableStructure->columns as $col_name => $col) {
            if (StringUtils::endsWith($col_name, "_i18n") && $col->auto == 0 && !StringUtils::startsWith($this->tableStructure->name, "i18n")) {
                $c .= StringUtils::println("if(" . $col->getAsVar("this") . " == null){", 2);
                $c .= StringUtils::println("\$li18n = new " . $this->i18n_class . "();", 3);
                $c .= StringUtils::println("\$li18n->name = \"" . $this->tableStructure->name . "_" . $col_name . "\";", 3);
                $c .= StringUtils::println("\$li18n->table= \"" . $this->tableStructure->name . "\";", 3);
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
        $c .= StringUtils::println("\$sql = \"INSERT INTO `$this->tableName` (" . implode(",", $this->tableStructure->getColumns("sql_name")) . ") VALUES (" . implode(",", $this->tableStructure->getColumns("sql_param")) . ")\";", 3);
        $c .= StringUtils::println("$this->prepare;", 3);
        $c .= StringUtils::println("\$bind_array = array(" . implode(",", $this->tableStructure->getColumns("bind_this")) . ");", 3);
        $c .= StringUtils::println("}else{", 2);
        $c .= StringUtils::println("\$sql = \"INSERT INTO `$this->tableName` (" . implode(",", $this->tableStructure->getNotAutoIncColumns("sql_name")) . ") VALUES (" . implode(",", $this->tableStructure->getNotAutoIncColumns("sql_param")) . ")\";", 3);
        $c .= StringUtils::println("$this->prepare;", 3);
        $c .= StringUtils::println("\$bind_array = array(" . implode(",", $this->tableStructure->getNotAutoIncColumns("bind_this")) . ");", 3);
        $c .= StringUtils::println("}", 2);
        $c .= StringUtils::println("if(" . sprintf($this->execute, "\$bind_array") . " === false){", 2);
        $c .= StringUtils::println("Log::getHandler()->error(\$result->errorInfo());", 3);
        $c .= StringUtils::println("return false;", 3);
        $c .= StringUtils::println("}else{", 2);
        foreach ($this->tableStructure->columns as $col_name => $col) {
            if ($col->auto > 0) {
                $c .= StringUtils::println("\$this->$col_name = (" . $col->type_php . ") Database::getHandler()->lastInsertId();", 3);
                $c .= StringUtils::println("\$this->generateResourceURI();", 3);
                break;
            }
        }
        if (isset($this->tableStructure->columns["password"])) {
            $c .= StringUtils::println("\$this->password=Encryption::password(\$this->password);", 3);
        }

        $c .= StringUtils::println("return true;", 3);
        $c .= StringUtils::println("}", 2);
        $c .= StringUtils::println("}", 1);
        return $c;
    }

}
