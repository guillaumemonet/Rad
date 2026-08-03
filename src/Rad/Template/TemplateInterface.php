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
     */
    public function display($filename = null, $cache_id = null, $compile_id = null, $parent = null);

    /**
     *
     */
    public function fetch($filename = null, $cache_id = null, $compile_id = null, $parent = null);

    /**
     *
     */
    public function isCached($filename = null, $cache_id = null, $compile_id = null, $parent = null);

    /**
     *
     */
    public function assign($varname, $value, $nocache = false);
}
