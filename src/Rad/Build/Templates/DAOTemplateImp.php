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
 * Description of DAOTemplateImp
 *
 * @author guillaume
 */
trait DAOTemplateImp {

    public $useImp = [];

    public function printImpNamespace() {
        return StringUtils::println('namespace ' . $this->namespaceImp . ';');
    }

    public function printImpUseClasses() {
        $this->useImp = array_merge($this->baseRequire, $this->useImp);
        $this->useImp[] = $this->namespace . "\\" . StringUtils::camelCase($this->className);
        return array_reduce($this->useController, function($carry, $item) {
            return $carry . StringUtils::println('use ' . $item . ';');
        });
    }

    public function printImpClassStart() {
        return StringUtils::printLn("class " . $this->className . "Imp extends " . $this->className . " {");
    }

    public function printImpSetterGetter() {
        $c = "";
        foreach ($this->tableStructure->columns as $col_name => $col) {
            if ($col->auto == 0 && !in_array($col_name, array("trash"))) {
                $c .= StringUtils::println("public function get" . StringUtils::camelCase($col_name) . "() :" . $col->type_php . "{", 1);
                $c .= StringUtils::println("return " . $col->getAsVar("this") . ";", 2);
                $c .= StringUtils::println("}", 1);

                $c .= StringUtils::println("public function set" . StringUtils::camelCase($col_name) . "(" . $col->type_php . " \$" . $col->name . ") {", 1);
                $c .= StringUtils::println($col->getAsVar("this") . "= \$" . $col->name . ";", 2);
                $c .= StringUtils::println("}", 1);
            }
        }
        return $c;
    }

    public function printImpAttributes() {
        $c = StringUtils::printLn("public \$resource_name = \"" . $this->className . "\";", 1);
        $c .= StringUtils::printLn("public \$resource_uri = null;", 1);
        $c .= StringUtils::printLn("public \$resource_namespace = __NAMESPACE__;", 1);
        foreach ($this->tableStructure->columns as $col_name => $col) {
            if (StringUtils::endsWith($col_name, "i18n") && $col->auto == 0 && !StringUtils::startsWith($this->tableStructure->name, "i18n")) {
                $c .= StringUtils::println("public \$" . str_replace("_i18n", "", $col_name) . "=[];", 1);
            }
            if (StringUtils::endsWith($col_name, "_id_picture")) {
                $c .= StringUtils::println("public \$" . str_replace("_id_picture", "", $col_name) . "=null;", 1);
            }
            $c .= StringUtils::println("public \$$col_name" . (isset($col->default) ? "=" . (is_numeric($col->default) ? $col->default : "\"" . $col->default . "\"") : "") . ";", 1);
        }
        return $c;
    }

}
