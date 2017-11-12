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

namespace Rad\bin\scripts;

use Rad\Utils\StringUtils;

/**
 * Description of GenerateImp
 *
 * @author guillaume
 */
trait GenerateImp {

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

}
