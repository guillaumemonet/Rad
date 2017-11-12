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
 * Description of GenerateController
 *
 * @author guillaume
 */
trait GenerateController {

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

}
