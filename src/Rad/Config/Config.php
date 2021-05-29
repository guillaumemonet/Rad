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

use Rad\Error\ConfigurationException;

/**
 *
 */
abstract class Config {

    public static $config = [];

    private function __construct() {
        
    }

    private function __clone() {
        
    }

    public static function loadDefaultConfig() {
        $datas        = file_get_contents(__DIR__ . "/../../../config/default_config.json");
        self::$config = json_decode($datas);
        if (json_last_error() > 0) {
            throw new ConfigurationException('Configuration default_config.json can\'t be loaded');
        }
    }

    /**
     * 
     * @param string $configDir
     * @throws ConfigurationException
     */
    public static function load(string $configDir = null) {
        if ($configDir === null) {
            self::loadDefaultConfig();
        } else {
            if (!file_exists($configDir . 'build_config.json')) {
                self::loadDefaultConfig();
                self::parseOtherConfigFiles($configDir);
                file_put_contents($configDir . 'build_config.json', json_encode(self::$config, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_FORCE_OBJECT));
            }
            self::$config = json_decode(file_get_contents($configDir . 'build_config.json'));
            if (json_last_error() > 0) {
                throw new ConfigurationException('Configuration build_config.json can\'t be loaded');
            }
        }
    }

    private static function parseOtherConfigFiles($configDir) {
        foreach (glob($configDir . "*.json") as $filename) {
            if (basename($filename) != "default_config.json") {
                $config = json_decode(file_get_contents($filename), true);
                if (json_last_error() > 0) {
                    throw new ConfigurationException('Configuration ' . $filename . ' can\'t be loaded');
                }
                self::$config = array_replace_recursive(self::$config, (array) $config);
            }
        }
    }

    /**
     * Return current config
     * @param string $section
     * @param string $row
     * @return string
     */
    public static function get(string $section, $row = null) {
        if ($row === null) {
            return isset(self::$config[$section]) ? self::$config[$section] : null;
        } else {
            return isset(self::$config[$section][$row]) ? self::$config[$section][$row] : null;
        }
    }

    public static function getConfig() {
        return self::$config;
    }

    /**
     * 
     * @param string $serviceType
     * @param string $serviceName
     * @return type
     */
    public static function getServiceConfig(string $serviceType, string $serviceName = null) {
        if ($serviceName === null) {
            return self::$config->services->{$serviceType};
        } else {
            return self::$config->services->{$serviceType}->handlers->{$serviceName};
        }
    }

    public static function getApiConfig($name = null) {
        if (!isset(self::$config->api)) {
            throw new \ErrorException('Not Api Config found');
        }
        return $name !== null ? self::$config->api->{$name} : self::$config->api;
    }

    /**
     *
     * @param type $section
     * @param type $row
     * @return type
     */
    public static function has(string $section, $row = null) {
        return $row === null ? isset(self::$config[$section]) : isset(self::$config[$section][$row]);
    }

    /**
     *
     * @param type $section
     * @param type $row
     * @param type $value
     */
    public static function set(string $section, string $row, $value) {
        self::$config[$section][$row] = $value;
    }

}
