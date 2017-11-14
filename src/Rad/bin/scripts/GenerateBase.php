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

use Rad\bin\scripts\templates\DAOTemplate;
use Rad\Log\Log;
use Rad\Utils\StringUtils;

final class GenerateBase {

    use GenerateTrait;
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

    public function generate() {
        $tables = $this->generateArrayTables();
        $this->generateClass($tables);
    }

    private function pathToNamespace($path) {
        return rtrim(str_replace("/", "\\", str_replace($this->basepath, '', $path)), "\\");
    }

    private function generateClass(array $tables) {
        foreach ($tables as $name => $table) {
            if (strpos($name, "_has_") !== false) {
                continue;
            }

            $class = StringUtils::camelCase($name);

            Log::getHandler()->debug('Generating ' . $class . ' to ' . $this->pathClass);
            $daomodel = new DAOTemplate($class, $name, $table);
            $daomodel->setClassNamespace($this->namespaceClass)
                    ->setControllerNamespace($this->namespaceController)
                    ->setImpNamespace($this->namespaceImp)
                    ->init();
            $this->printClass($daomodel);
            $this->printTrait($daomodel);
            $this->printController($daomodel);
            $this->printImp($daomodel);
        }
    }

    public function printClass(DAOTemplate $daomodel) {
        $filenameClass = $this->pathClass . '/' . $daomodel->className . '.php';
        file_exists($filenameClass) ? unlink($filenameClass) : '';
        $fileClass = fopen($filenameClass, "w+");
        $c = StringUtils::println("<?php");
        $c .= $daomodel->printNamespace();
        $c .= $daomodel->printUseClasses();
        $c .= $daomodel->printStartClass();
        $c .= $daomodel->printAttributes();
        $c .= $daomodel->printContructor();
        $c .= $daomodel->printGetId();
        $c .= $daomodel->printToString();
        $c .= $daomodel->printParse();
        $c .= $daomodel->printCreate();
        $c .= $daomodel->printRead();
        $c .= $daomodel->printUpdate();
        $c .= $daomodel->printDelete();
        $c .= $daomodel->printOneToMany();
        $c .= $daomodel->printManyToMany();
        $c .= $daomodel->printEndClass();
        fwrite($fileClass, $c);
    }

    public function printTrait(DAOTemplate $daomodel) {
        $filenameTrait = $this->pathClass . '/' . $daomodel->className . 'Trait.php';
        file_exists($filenameTrait) ? unlink($filenameTrait) : '';
        $fileTrait = fopen($filenameTrait, "w+");
        $c = StringUtils::println("<?php");
        $c .= $daomodel->printNamespace();
        $c .= $daomodel->printUseClasses();
        $c .= $daomodel->printStartTrait();
        $c .= $daomodel->printIndexesGetter();
        $c .= $daomodel->printEndClass();
        fwrite($fileTrait, $c);
    }

    public function printController(DAOTemplate $daomodel) {
        $filenameController = $this->pathController . '/' . $daomodel->className . 'BaseController.php';
        file_exists($filenameController) ? unlink($filenameController) : '';
        $fileController = fopen($filenameController, "w+");
        $c = StringUtils::println("<?php");
        $c .= $daomodel->printControllerNamespace();
        $c .= $daomodel->printControllerUseClasses();
        $c .= $daomodel->printStartController();
        $c .= $daomodel->printControllerGetAll();
        $c .= $daomodel->printControllerGetOne();
        $c .= $daomodel->printControllerPostOne();
        $c .= $daomodel->printControllerDeleteOne();
        $c .= $daomodel->printControllerPutOne();
        $c .= $daomodel->printControllerPatchOne();
        $c .= $daomodel->printEndClass();
        fwrite($fileController, $c);
    }

    public function printImp(DAOTemplate $daomodel) {
        $filenameImp = $this->pathImp . '/' . $daomodel->className . 'Imp.php';
        file_exists($filenameImp) ? unlink($filenameImp) : '';
        $fileImp = fopen($filenameImp, "w+");
        $c = StringUtils::println("<?php");
        $c .= $daomodel->printImpNamespace();
        $c .= $daomodel->printImpUseClasses();
        $c .= $daomodel->printImpClassStart();
        $c .= $daomodel->printImpAttributes();
        $c .= $daomodel->printImpSetterGetter();
        $c .= $daomodel->printEndClass();
        fwrite($fileImp, $c);
    }

}
