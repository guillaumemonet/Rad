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
 * Description of DAOTemplateController
 *
 * @author guillaume
 */
trait DAOTemplateController {

    protected $useController = array(
        'Rad\\Http\\Request',
        'Rad\\Http\\Response',
        'Rad\\Route\\Route',
        'Rad\\Controller\\Controller'
    );

    public function printControllerNamespace() {
        return StringUtils::println('namespace ' . $this->namespaceController . ';');
    }

    public function printStartController() {
        return StringUtils::println('class ' . $this->className . 'BaseController extends Controller {');
    }

    public function printControllerUseClasses() {
        $this->useController = array_merge($this->baseRequire, $this->useController);
        $this->useController[] = $this->namespace . "\\" . StringUtils::camelCase($this->className);
        return array_reduce($this->useController, function($carry, $item) {
            return $carry . StringUtils::println('use ' . $item . ';');
        });
    }

    public function printControllerGetAll() {
        $c = $this->printComments("get", "/^" . $this->tableName . "$/");
        $c .= StringUtils::println('public function getAll' . $this->className . '(){', 1);
        $c .= StringUtils::println('return ' . $this->className . "::getAll" . $this->className . '($this->getRequest()->offset, $this->getRequest()->limit, $this->getRequest()->isCache(), $this->getRequest()->get_datas);', 2);
        $c .= StringUtils::println("}", 1);
        return $c;
    }

    public function printControllerGetOne() {
        $c = $this->printComments("get", "/^" . $this->tableName . "\/([0-9]*)$/");
        $c .= StringUtils::println("public function get" . $this->className . "(){", 1);
        $c .= StringUtils::println('return ' . $this->className . "::get" . $this->className . '($this->getRoute()->getArgs()[0],$this->getRequest()->isCache());', 2);
        $c .= StringUtils::println("}", 1);
        return $c;
    }

    public function printControllerPostOne() {
        $c = $this->printComments("post", "/^" . $this->tableName . "\/([0-9]*)$/");
        $c .= StringUtils::println("public function post" . $this->className . "(){", 1);
        $c .= StringUtils::println("}", 1);
        return $c;
    }

    public function printControllerPutOne() {
        $c = $this->printComments("put", "/^" . $this->tableName . "\/([0-9]*)$/");
        $c .= StringUtils::println("public function put" . $this->className . "(){", 1);
        $c .= StringUtils::println("}", 1);
        return $c;
    }

    public function printControllerPatchOne() {
        $c = $this->printComments("patch", "/^" . $this->tableName . "\/([0-9]*)$/");
        $c .= StringUtils::println("public function patch" . $this->className . "(){", 1);
        $c .= StringUtils::println("}", 1);
        return $c;
    }

    public function printControllerDeleteOne() {
        $c = $this->printComments("delete", "/^" . $this->tableName . "\/([0-9]*)$/");
        $c .= StringUtils::println("public function delete" . $this->className . "(){", 1);
        $c .= StringUtils::println("}", 1);
        return $c;
    }

    public function printComments($method, $regex) {
        $c = StringUtils::println("/**", 1);
        $c .= StringUtils::println(" * @api 1", 1);
        $c .= StringUtils::println(" * @" . $method . " " . $regex, 1);
        $c .= StringUtils::println(" * @produce json", 1);
        $c .= StringUtils::println(" */", 1);
        return $c;
    }

}
