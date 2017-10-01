<?php

namespace Rad\manager;

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

final class Cookies implements \Psr\Container\ContainerInterface {

    private function __construct() {
	
    }

    /**
     * Set the cookie to the user.
     *
     * @param string $name
     * @param string $url
     * @param string $value
     * @param string $path
     */
    public static function set($name, $url, $value, $path = '/') {
	if (!setcookie($name, $value, (time() + (int) Config::get('cookie', 'lifetime')), $path, $url)) {
	    Log::getLogger()->error('Error Setting Cookie For ' . $url . ' Value: ' . $value);
	    return false;
	}
	return true;
    }

    /**
     * Return cookie's value.
     *
     * @param string $name
     *
     * @return string
     */
    public static function get($name) {
	if (isset($_COOKIE[$name])) {
	    return $_COOKIE[$name];
	} else {
	    return null;
	}
    }

}
