<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Template;

use Rad\Config\Config;
use Smarty;

/**
 *
 */
class SmartyTemplateHandler extends Smarty implements TemplateInterface {
    public function __construct() {
        parent::__construct();
        $config                = Config::getServiceConfig('template', 'smarty')->config;
        $installPath           = Config::getApiConfig()->install_path;
        $this->compile_check   = (int) $config->compile_check;
        $this->force_compile   = (bool) $config->force_compile;
        $this->debugging       = (bool) $config->debugging;
        $this->error_reporting = (int) $config->error_reporting;
        $this->caching         = (int) $config->caching;
        $this->cache_locking   = true;
        $this->cache_lifetime  = (int) $config->cache_lifetime;
        $this->setTemplateDir($installPath . $config->template_dir);
        $this->setCompileDir($installPath . $config->compile_dir);
        $this->setConfigDir($installPath . $config->config_dir);
        $this->setCacheDir($installPath . $config->cache_dir);
        if ($config->cache_type !== 'smarty') {
            $this->caching_type = $config->cache_type;
            $this->registerCacheResource($config->cache_type, new SmartyTemplateCacheHandler($config->cache_type));
        }
    }

}
