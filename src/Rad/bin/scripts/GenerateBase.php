<?php

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

namespace Rad\bin\scripts;

final class GenerateBase {

    use GenerateTrait;
    use GenerateClass;
    use GenerateImp;
    use CommonClassTrait;

    private $database = null;
    private $basepath = null;
    private $pathClass = null;
    private $pathController = null;
    private $pathImp = null;
    private $namespaceClass = null;
    private $namespaceController = null;
    private $namespaceImp = null;

    public function __construct() {
        $this->generateClassName();
    }

    public function setDatabase(string $database) {
        $this->database = $database;
        return $this;
    }

    public function setBasePath(string $basepath) {
        $this->basepath = $basepath;
        return $this;
    }

    public function setDaoPath(string $daopath) {
        $this->pathClass = $this->basepath . '/' . $daopath;
        $this->namespaceClass = $this->pathToNamespace($daopath);
        return $this;
    }

    public function setControllerPath(string $controllerpath) {
        $this->pathController = $this->basepath . '/' . $controllerpath;
        $this->namespaceController = $this->pathToNamespace($controllerpath);
        return $this;
    }

    public function setImpPath(string $imppath) {
        $this->pathImp = $this->basepath . '/' . $imppath;
        $this->namespaceImp = $this->pathToNamespace($imppath);
        return $this;
    }

    public function makeDir($force = false) {
        mkdir($this->pathClass, 0777, $force);
        mkdir($this->pathController, 0777, $force);
        mkdir($this->pathImp, 0777, $force);
        return $this;
    }

    public function generate(bool $generateImp = false) {
        $tables = $this->generateArrayTables();
        $this->generateClass($tables);

        /* if ($generateImp) {
          $this->generateImp();
          } */
        
    }

    private function pathToNamespace($path) {
        return rtrim(str_replace("/", "\\", str_replace($this->basepath, '', $path)), "\\");
    }

}
