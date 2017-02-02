<?php

namespace Rad\Template;

use Rad\Config\Config;

/*
 * Copyright (C) 2016 Guillaume Monet
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

final class Template {

    /**
     * @var Smarty
     */
    private static $smarty = null;

    private function __construct() {
        
    }

    private static function init() {
        require_once('libs/smarty/Smarty.class.php');
        self::$smarty = new \Smarty();
        self::$smarty->compile_check = (int) Config::get('template', 'compile_check');
        self::$smarty->force_compile = (int) Config::get('template', 'force_compile');
        self::$smarty->debugging = (int) Config::get('template', 'debugging');
        self::$smarty->error_reporting = (int) Config::get('template', 'error_reporting');
        self::$smarty->caching = (int) Config::get('template', 'caching');
        self::$smarty->cache_locking = 1;
        self::$smarty->cache_lifetime = (int) Config::get('template', 'cache_lifetime');
        self::$smarty->registerDefaultTemplateHandler('Rad\Template\Template::getDefault_template');
        self::$smarty->template_dir = Config::get('install', 'path') . Config::get('template', 'template_dir');
        self::$smarty->compile_dir = Config::get('install', 'path') . Config::get('template', 'compile_dir');
        self::$smarty->config_dir = Config::get('install', 'path') . Config::get('template', 'config_dir');
        self::$smarty->cache_dir = Config::get('install', 'path') . Config::get('template', 'cache_dir');
    }

    /**
     * 
     * @param type $seller_slug
     * @param type $filename
     * @param array $params
     * @param type $cache_id
     * @param type $compile_id
     */
    public static function display($filename, $cache_id = null, $compile_id = null) {
        if (self::$smarty === null) {
            self::init();
        }
        self::$smarty->display($filename, $cache_id, $compile_id);
    }

    /**
     * 
     * @param type $seller_slug
     * @param type $filename
     * @param array $params
     * @param type $cache_id
     * @param type $compile_id
     */
    public static function fetch($filename, $cache_id = null, $compile_id = null) {
        if (self::$smarty === null) {
            self::init();
        }
        self::$smarty->fetch($filename, $cache_id, $compile_id);
    }

    /**
     * 
     * @param type $seller_slug
     * @param type $univers
     * @param type $filename
     * @param type $cache_id
     * @param type $compile_id
     * @return type
     */
    public static function isCached($filename, $cache_id = null, $compile_id = null) {
        if (self::$smarty === null) {
            self::init();
        }
        return self::$smarty->isCached($filename, $cache_id, $compile_id);
    }

    /**
     * @param string $varname
     * @param string $value
     */
    public static function assign($varname, $value) {
        if (self::$smarty === null) {
            self::init();
        }
        self::$smarty->assign($varname, $value);
    }

    /**
     * 
     * @param type $resource_type
     * @param type $resource_name
     * @param type $template_source
     * @param type $template_timestamp
     * @param type $smarty_obj
     * @return boolean
     */
    public static function getDefault_template($resource_type, $resource_name, &$template_source, &$template_timestamp, $smarty_obj) {
        $tpl = Config::get('install', 'path') . Config::get('template', 'template_dir') . $resource_name;
        error_log($tpl);
        if ($resource_type == 'file' && is_file($tpl)) {
            if ($fp = fopen($tpl, 'r')) {
                $template_source = fread($fp, filesize($tpl));
                $template_timestamp = filemtime($tpl);
                fclose($fp);
                return true;
            }
        }
        return false;
    }

}
