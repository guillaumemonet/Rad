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
 * Description of DAOTemplateUpdateTrait
 *
 * @author guillaume
 */
trait DAOTemplateUpdateTrait {

    public function printUpdate() {
        $c = StringUtils::println("public function update(){", 1);
        if (isset($this->tableStructure->columns["slug"]) && isset($this->tableStructure->columns["name"])) {
            $c .= StringUtils::println("\$this->slug=StringUtils::slugify(\$this->name);", 2);
        }

        $c .= StringUtils::println("\$sql = \"UPDATE `$this->tableName` SET " . implode(",", $this->tableStructure->getNotPrimaryColumns("sql_cond", true)) . " WHERE " . implode(" AND ", $this->tableStructure->getPrimaryColumns("sql_cond")) . "\";", 2);
        $c .= StringUtils::println("$this->prepare;", 2);

        foreach ($this->tableStructure->columns as $col_name => $col) {
            if (StringUtils::endsWith($col_name, "_i18n") && $col->auto == 0 && !StringUtils::startsWith($this->tableStructure->name, "i18n")) {
                //TODO Rajouter les conditions de test pour la creation d'une trad
                $c .= StringUtils::println("foreach(\$this->" . str_replace("_i18n", "", $col_name) . " as \$lang=>\$datas){", 2);
                $c .= StringUtils::println("\$ti18n = " . $this->i18n_translate_class . "::getTranslation(" . $col->getAsVar("this") . ",\$lang);", 3);
                $c .= StringUtils::println("\$ti18n->datas=\$datas;", 3);
                $c .= StringUtils::println("\$ti18n->update();", 3);
                $c .= StringUtils::println("}", 2);
            }
        }

        $c .= StringUtils::println("return " . sprintf($this->execute, "array(" . implode(",", $this->tableStructure->getColumns("bind_this", true))) . ");", 2);
        $c .= StringUtils::println("}", 1);
        return $c;
    }

}
