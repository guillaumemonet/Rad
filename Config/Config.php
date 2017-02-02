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

namespace Rad\Config;

/**
 * 
 */
final class Config {

    private static $config = array();

    private function __construc() {
        
    }

    /**
     * Load config file.
     *
     * @param string $path
     */
    public static function load($path = null) {
        if ($path === null) {
            $filename = __DIR__ . DIRECTORY_SEPARATOR . "Shelter/Config/config.ini";
        } else {
            $filename = $path;
        }
        self::$config = array_merge(self::$config, parse_ini_file($filename, true));
    }

    /**
     * Return current config 
     * @param string $section
     * @param string $row
     * @return string
     */
    public static function get($section, $row = null) {
        if (count(self::$config) === 0) {
            self::load();
        }
        if ($row == null) {
            if (isset(self::$config[$section])) {
                return self::$config[$section];
            } else {
                return null;
            }
        } else {
            if (isset(self::$config[$section][$row])) {
                return self::$config[$section][$row];
            } else {
                return null;
            }
        }
    }

}
