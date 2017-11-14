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
 * Description of LanguageTrait
 *
 * @author guillaume
 */
trait CommonClassTrait {

    protected $baseRequire = array(
        "PDO",
        "Rad\\Model\\Model",
        "Rad\\Model\\ModelDAO",
        "Rad\\Database\\Database",
        "Rad\\Cache\\Cache",
        "Rad\\Log\\Log",
        "Rad\\Utils\\StringUtils",
        "Rad\\Encryption\\Encryption"
    );
    protected $i18n_translate_table = "i18n_translate";
    protected $i18n_table = "i18n";
    protected $i18n_translate_class;
    protected $i18n_class;
    protected $picture_table = "picture";
    protected $picture_class;
    protected $language_table = "language";
    protected $language_class;

    public function generateClassName() {
        $this->i18n_class = StringUtils::camelCase($this->i18n_table);
        $this->i18n_translate_class = StringUtils::camelCase($this->i18n_translate_table);
        $this->picture_class = StringUtils::camelCase($this->picture_table);
        $this->language_class = StringUtils::camelCase($this->language_table);
        if (StringUtils::camelCase($this->tableName) != $this->i18n_translate_class) {
            $this->addUseClasse($this->namespace . "\\" . $this->i18n_translate_class);
        }
        if (StringUtils::camelCase($this->tableName) != $this->i18n_class) {
            $this->addUseClasse($this->namespace . "\\" . $this->i18n_class);
        }
        if (StringUtils::camelCase($this->tableName) != $this->picture_class) {
            $this->addUseClasse($this->namespace . "\\" . $this->picture_class);
        }
        if (StringUtils::camelCase($this->tableName) != $this->language_class) {
            $this->addUseClasse($this->namespace . "\\" . $this->language_class);
        }
    }

    public function addUseClasse(string $classname) {
        $this->baseRequire[] = $classname;
        return $this;
    }

}
