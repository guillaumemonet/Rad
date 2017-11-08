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

namespace Rad\Template;

use Rad\Config\Config;
use Smarty;

final class Smarty_TemplateHandler extends Smarty implements TemplateInterface {

    public function __construct() {
        parent::__construct();
        $this->compile_check = (int) Config::get('template', 'compile_check');
        $this->force_compile = (int) Config::get('template', 'force_compile');
        $this->debugging = (int) Config::get('template', 'debugging');
        $this->error_reporting = (int) Config::get('template', 'error_reporting');
        $this->caching = (int) Config::get('template', 'caching');
        $this->cache_locking = 1;
        $this->cache_lifetime = (int) Config::get('template', 'cache_lifetime');
        $this->template_dir = Config::get('install', 'path') . Config::get('template', 'template_dir');
        $this->compile_dir = Config::get('install', 'path') . Config::get('template', 'compile_dir');
        $this->config_dir = Config::get('install', 'path') . Config::get('template', 'config_dir');
        $this->cache_dir = Config::get('install', 'path') . Config::get('template', 'cache_dir');
    }

}
