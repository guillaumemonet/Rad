<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Config;

use Rad\Encryption\Encryption;
use Rad\Error\ConfigurationException;
use Rad\Log\Log;

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
        $configFile = $configDir . 'build_config.json';
        if (!file_exists($configFile)) {
            self::loadDefaultConfig();
            $md5                                = self::parseOtherConfigFiles($configDir);
            self::generateToken();
            self::$config['api']['config_date'] = $md5;
            file_put_contents($configFile, json_encode(self::$config, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_FORCE_OBJECT));
        }
        self::$config = json_decode(file_get_contents($configFile));
        if (json_last_error() > 0) {
            throw new ConfigurationException('Configuration build_config.json can\'t be loaded');
        }
        self::checkConfigModification($configDir, $configFile);
    }

    private static function checkConfigModification($configDir, $configFile) {
        $stringTime = "";
        foreach (glob($configDir . "*.json") as $filename) {
            if (basename($filename) != "build_config.json") {
                $stringTime .= filemtime($filename);
            }
        }
        $md5 = md5($stringTime);
        if (!isset(self::$config->api->config_date) || self::$config->api->config_date != $md5) {
            unlink($configFile);
            Log::getHandler()->debug("Regenerate config file");
            self::buildJsonConfig($configDir);
        }
    }

    private static function parseOtherConfigFiles($configDir) {
        $stringTime   = "";
        self::$config = array_reduce(glob($configDir . "*.json"), function ($config, $filename) use (&$stringTime) {
            if (basename($filename) != "build_config.json") {
                $stringTime .= filemtime($filename);
                $fileConfig = self::loadOtherConfig($filename);
                if ($fileConfig !== null) {
                    $arrayConfig       = (array) $config;
                    $arrayCustomConfig = (array) $fileConfig;
                    $config            = self::array_merge_recursive_distinct($arrayConfig, $arrayCustomConfig);
                }
            }
            return $config;
        }, self::$config);
        return md5($stringTime);
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
        if (basename($filename) != "build_config.json") {
            error_log("Loading " . $filename);
            $fileConfig = json_decode(file_get_contents($filename), true);
            if (json_last_error() > 0) {
                throw new ConfigurationException('Configuration ' . $filename . ' can\'t be loaded');
            }
        }
        return $fileConfig;
    }

    private static function generateToken() {
        if (!isset(self::$config['api']['token'])) {
            self::$config['api']['token'] = Encryption::generateToken(16);
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
            throw new ConfigurationException('Not Api Config found');
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
