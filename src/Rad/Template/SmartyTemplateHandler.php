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

use Rad\Cache\Cache;
use Rad\Config\Config;
use Smarty;

class SmartyTemplateHandler extends Smarty implements TemplateInterface {

    public function __construct() {
        parent::__construct();
        $config                = Config::getServiceConfig('template', 'smarty')->config;
        $this->compile_check   = (int) $config->compile_check;
        $this->force_compile   = (int) $config->force_compile;
        $this->debugging       = (int) $config->debugging;
        $this->error_reporting = (int) $config->error_reporting;
        $this->caching         = (int) $config->caching;
        $this->cache_locking   = 1;
        $this->cache_lifetime  = (int) $config->cache_lifetime;
        $this->template_dir    = $config->template_dir;
        $this->compile_dir     = $config->compile_dir;
        $this->config_dir      = $config->config_dir;
        $this->cache_dir       = $config->cache_dir;
    }

    public function setCacheResource(string $name, Cache $cache) {
        $this->registerCacheResource($name, $cache);
        $this->caching_type = $name;
    }

}
