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
 * Description of DAOTemplateParseTrait
 *
 * @author guillaume
 */
trait DAOTemplateParseTrait {

    public function printParse() {
        $c = StringUtils::println("public function parse(\$row,\$use_cache) {", 1);
        foreach ($this->tableStructure->columns as $col_name => $col) {
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
        foreach ($this->tableStructure->columns as $col_name => $col) {
            if (StringUtils::endsWith($col_name, "_i18n") && $col->auto == 0 && !StringUtils::startsWith($this->tableStructure->name, "i18n")) {
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
        return $c;
    }

}
