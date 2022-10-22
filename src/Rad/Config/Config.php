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

    /**
     * 
     * @param string $configDir
     * @throws ConfigurationException
     */
    public static function load(string $configDir = null) {
        if ($configDir === null) {
            self::loadDefaultConfig();
        } else {
            if (!is_dir($configDir)) {
                throw new ConfigurationException("Not a directory : " . $configDir);
            }
            $dir = dir($configDir);
            self::buildJsonConfig($dir->path . "/");
        }
    }

    private static function buildJsonConfig($configDir) {
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

    private static function parseOtherConfigFiles($configDir) {
        self::$config = array_reduce(glob($configDir . "*.json"), function ($config, $filename) {
            $fileConfig = self::loadOtherConfig($filename);
            if ($fileConfig !== null) {
                $arrayConfig       = (array) $config;
                $arrayCustomConfig = (array) $fileConfig;
                $config            = self::array_merge_recursive_distinct($arrayConfig, $arrayCustomConfig);
            }
            return $config;
        }, self::$config);
    }

    private static function array_merge_recursive_distinct(array &$default, array &$custom) {
        $merged = $default;
        foreach ($custom as $key => &$value) {
            if (is_array($value) && isset($merged [$key]) && is_array($merged [$key])) {
                $merged [$key] = self::array_merge_recursive_distinct($merged [$key], $value);
            } else {
                $merged [$key] = $value;
            }
        }
        return $merged;
    }

    public static function loadDefaultConfig() {
        $datas        = file_get_contents(__DIR__ . "/../../../config/default_config.json");
        self::$config = json_decode($datas, true);
        if (json_last_error() > 0) {
            throw new ConfigurationException('Configuration default_config.json can\'t be loaded');
        }
    }

    private static function loadOtherConfig($filename) {
        $fileConfig = null;
        if (basename($filename) != "default_config.json") {
            error_log("Loading " . $filename);
            $fileConfig = json_decode(file_get_contents($filename), true);
            if (json_last_error() > 0) {
                throw new ConfigurationException('Configuration ' . $filename . ' can\'t be loaded');
            }
        }
        return $fileConfig;
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
