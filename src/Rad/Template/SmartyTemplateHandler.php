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
        $this->compile_check   = (int) $config->compile_check;
        $this->force_compile   = (int) $config->force_compile;
        $this->debugging       = (int) $config->debugging;
        $this->error_reporting = (int) $config->error_reporting;
        $this->caching         = (int) $config->caching;
        $this->cache_locking   = 1;
        $this->cache_lifetime  = (int) $config->cache_lifetime;
        $this->template_dir    = Config::getApiConfig()->install_path . $config->template_dir;
        $this->compile_dir     = Config::getApiConfig()->install_path . $config->compile_dir;
        $this->config_dir      = Config::getApiConfig()->install_path . $config->config_dir;
        $this->cache_dir       = Config::getApiConfig()->install_path . $config->cache_dir;
        if ($config->cache_type !== 'smarty') {
            $this->caching_type = $config->cache_type;
            $this->registerCacheResource($config->cache_type, new SmartyTemplateCacheHandler($config->cache_type));
        }
    }

}
