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
     * @param string $filename
     */
    public static function load($filename) {
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

    /**
     * 
     * @param type $section
     * @param type $row
     * @param type $value
     */
    public static function set($section, $row, $value) {
        self::$config[$section][$row] = $value;
    }

}
