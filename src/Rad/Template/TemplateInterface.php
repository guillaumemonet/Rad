<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Template;

/**
 * Description of TemplateInterface
 *
 * @author guillaume
 */
interface TemplateInterface {

    /**
     * 
     * @param type $filename
     * @param type $cache_id
     * @param type $compile_id
     */
    public function display($filename = null, $cache_id = null, $compile_id = null, $parent = null);

    /**
     * 
     * @param type $filename
     * @param type $cache_id
     * @param type $compile_id
     */
    public function fetch($filename = null, $cache_id = null, $compile_id = null, $parent = null);

    /**
     * 
     * @param type $filename
     * @param type $cache_id
     * @param type $compile_id
     */
    public function isCached($filename = null, $cache_id = null, $compile_id = null, $parent = null);

    /**
     * 
     * @param type $varname
     * @param type $value
     */
    public function assign($varname, $value, $nocache = false);
}
