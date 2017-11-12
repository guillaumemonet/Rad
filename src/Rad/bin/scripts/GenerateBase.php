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

use Rad\bin\scripts\elements\Column;
use Rad\bin\scripts\elements\Table;
use Rad\Database\Database;
use Rad\Utils\StringUtils;

final class GenerateBase {

    use GenerateTrait;
    use GenerateClass;
    use GenerateService;
    use GenerateController;
    use GenerateImp;

    private $database = null;
    private $pathClass = null;
    private $pathService = null;
    private $pathImp = null;
    private $namespaceClass = null;
    private $namespaceService = null;
    private $namespaceImp = null;
    private $baseRequire = array(
        "PDO",
        "Rad\\Model\\Model",
        "Rad\\Model\\ModelDAO",
        "Rad\\Database\\Database",
        "Rad\\Cache\\Cache",
        "Rad\\Log\\Log",
        "Rad\\Utils\\StringUtils",
        "Rad\\Encryption\\Encryption"
    );
    private $i18n_translate_table = "i18n_translate";
    private $i18n_table = "i18n";
    private $i18n_translate_class;
    private $i18n_class;
    private $picture_table = "picture";
    private $picture_class;
    private $language_table = "language";
    private $language_class;

    public function __construct($database = "bb", $dir = "", $basePath = "", $prefixClassesPath = "", $prefixServicesPath = "", $prefixImpsPath = "") {
        $this->database = $database;
        $this->pathClass = $basePath . "/" . $prefixClassesPath;
        $this->namespaceClass = rtrim(str_replace("/", "\\", $this->pathClass), "\\");
        $this->pathService = $basePath . "/" . $prefixServicesPath;
        $this->namespaceService = rtrim(str_replace("/", "\\", $this->pathService), "\\");
        $this->pathImp = $basePath . "/" . $prefixImpsPath;
        $this->namespaceImp = rtrim(str_replace("/", "\\", $this->pathImp), "\\");
        $this->i18n_class = StringUtils::camelCase($this->i18n_table);
        $this->i18n_translate_class = StringUtils::camelCase($this->i18n_translate_table);
        $this->picture_class = StringUtils::camelCase($this->picture_table);
        $this->language_class = StringUtils::camelCase($this->language_table);
        $this->pathClass = $dir . "/" . $this->pathClass;
        $this->pathImp = $dir . "/" . $this->pathImp;
        $this->pathService = $dir . "/" . $this->pathService;
    }

    public function generate(bool $generateImp = false) {
        mkdir($this->pathClass, 0777, true);
        mkdir($this->pathService, 0777, true);
        $this->generateClass();
        $this->generateServices();
        if ($generateImp) {
            $this->generateImp();
        }
        $this->generateController();
    }

}
