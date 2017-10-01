<?php

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

namespace Rad\manager;

use Exception;

/**
 * Description of Module
 *
 * @author Guillaume Monet
 */
final class Module {

    private function __construct() {
	
    }

    /**
     * 
     * @param string $module
     * @param type $base
     * @return IModule
     */
    public static function load(string $module, $base = false) {
	$file = Config::getRootPath() . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $module . ".php";
	require($file);
	$namespace_mod = "modules\\" . $module . "\\" . $module;
	try {
	    $mod = new $namespace_mod;
	    return $mod;
	} catch (Exception $ex) {
	    Log::getLogger()->error("Module " . $module . " not found");
	    return null;
	}
    }

}
