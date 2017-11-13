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
    use GenerateController;
    use GenerateImp;
    use CommonClassTrait;

    private $database = null;
    private $basepath = null;
    private $pathClass = null;
    private $pathService = null;
    private $pathImp = null;
    private $namespaceClass = null;
    private $namespaceService = null;
    private $namespaceImp = null;

    public function __construct($database = "bb", $dir = "", $basePath = "", $prefixClassesPath = "", $prefixServicesPath = "", $prefixImpsPath = "") {
        $this->database = $database;
        $this->pathClass = $basePath . "/" . $prefixClassesPath;
        $this->namespaceClass = rtrim(str_replace("/", "\\", $this->pathClass), "\\");
        $this->pathService = $basePath . "/" . $prefixServicesPath;
        $this->namespaceService = rtrim(str_replace("/", "\\", $this->pathService), "\\");
        $this->pathImp = $basePath . "/" . $prefixImpsPath;
        $this->namespaceImp = rtrim(str_replace("/", "\\", $this->pathImp), "\\");
        $this->pathClass = $dir . "/" . $this->pathClass;
        $this->pathImp = $dir . "/" . $this->pathImp;
        $this->pathService = $dir . "/" . $this->pathService;
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
        
    }

    public function generate(bool $generateImp = false) {
        $tables = $this->generateArrayTables();
        mkdir($this->pathClass, 0777, true);
        mkdir($this->pathService, 0777, true);
        $this->generateClass($tables);

        /* if ($generateImp) {
          $this->generateImp();
          }
          $this->generateController(); */
    }

}
