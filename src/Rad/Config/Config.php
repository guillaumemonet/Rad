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
use stdClass;

/**
 * Application configuration store.
 *
 * The build pipeline works on plain arrays (merge / token / json_encode),
 * but at runtime {@see self::$config} is ALWAYS a stdClass so every consumer
 * can rely on object access (e.g. Config::getConfig()->api->token).
 */
abstract class Config {

    const BUILD_NAME = 'build_config.json';

    public static ?stdClass $config = null;

    private function __construct() {

    }

    private function __clone() {

    }

    /**
     * @param string|null $configDir
     * @throws ConfigurationException
     */
    public static function load(?string $configDir = null): void {
        if ($configDir === null) {
            self::$config = self::toObject(self::defaultConfigArray());
            return;
        }
        if (!is_dir($configDir)) {
            throw new ConfigurationException('Not a directory : ' . $configDir);
        }
        self::buildJsonConfig(rtrim($configDir, '/\\') . '/');
    }

    private static function buildJsonConfig(string $configDir): void {
        $configFile = $configDir . self::BUILD_NAME;
        if (!file_exists($configFile)) {
            [$config, $md5]               = self::mergeOtherConfigFiles(self::defaultConfigArray(), $configDir);
            $config                       = self::generateToken($config);
            $config['api']['config_date'] = $md5;
            file_put_contents($configFile, json_encode($config, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_FORCE_OBJECT));
        }
        $decoded = json_decode(file_get_contents($configFile));
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ConfigurationException('Configuration ' . self::BUILD_NAME . ' can\'t be loaded');
        }
        self::$config = $decoded;
        self::checkConfigModification($configDir, $configFile);
    }

    private static function checkConfigModification(string $configDir, string $configFile): void {
        $stringTime = "";
        foreach (glob($configDir . '*.json') as $filename) {
            if (basename($filename) != self::BUILD_NAME) {
                $stringTime .= filemtime($filename);
            }
        }
        $md5 = md5($stringTime);
        if (!isset(self::$config->api->config_date) || self::$config->api->config_date != $md5) {
            unlink($configFile);
            Log::getHandler()->debug('Regenerate config file');
            self::buildJsonConfig($configDir);
        }
    }

    /**
     * Merge every *.json of the config dir on top of the defaults.
     *
     * @return array{0: array, 1: string} the merged config and the mtime hash
     */
    private static function mergeOtherConfigFiles(array $config, string $configDir): array {
        $stringTime = "";
        foreach (glob($configDir . '*.json') as $filename) {
            if (basename($filename) === self::BUILD_NAME) {
                continue;
            }
            $stringTime .= filemtime($filename);
            $fileConfig = self::loadOtherConfig($filename);
            if ($fileConfig !== null) {
                $config = self::array_merge_recursive_distinct($config, $fileConfig);
            }
        }
        return [$config, md5($stringTime)];
    }

    private static function array_merge_recursive_distinct(array $default, array $custom): array {
        $merged = $default;
        foreach ($custom as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::array_merge_recursive_distinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }
        return $merged;
    }

    private static function defaultConfigArray(): array {
        $datas  = file_get_contents(__DIR__ . "/../../../config/default_config.json");
        $config = json_decode($datas, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ConfigurationException('Configuration default_config.json can\'t be loaded');
        }
        return $config;
    }

    private static function loadOtherConfig(string $filename): ?array {
        if (basename($filename) === self::BUILD_NAME) {
            return null;
        }
        $fileConfig = json_decode(file_get_contents($filename), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ConfigurationException('Configuration ' . $filename . ' can\'t be loaded');
        }
        return $fileConfig;
    }

    private static function generateToken(array $config): array {
        if (!isset($config['api']['token'])) {
            $config['api']['token'] = Encryption::generateToken(16);
        }
        return $config;
    }

    private static function toObject(array $config): stdClass {
        return json_decode(json_encode($config));
    }

    /**
     * Return a configuration section, or a single row inside it.
     *
     * @return mixed the value, or null when absent
     */
    public static function get(string $section, ?string $row = null): mixed {
        if (!isset(self::$config->{$section})) {
            return null;
        }
        $sectionValue = self::$config->{$section};
        if ($row === null) {
            return $sectionValue;
        }
        return $sectionValue->{$row} ?? null;
    }

    public static function getConfig(): ?stdClass {
        return self::$config;
    }

    /**
     * @throws ConfigurationException
     */
    public static function getServiceConfig(string $serviceType, ?string $serviceName = null): mixed {
        if (!isset(self::$config->services->{$serviceType})) {
            throw new ConfigurationException('No config found for service ' . $serviceType);
        }
        if ($serviceName === null) {
            return self::$config->services->{$serviceType};
        }
        return self::$config->services->{$serviceType}->handlers->{$serviceName};
    }

    /**
     * @throws ConfigurationException
     */
    public static function getApiConfig(?string $name = null): mixed {
        if (!isset(self::$config->api)) {
            throw new ConfigurationException('No Api Config found');
        }
        return $name !== null ? (self::$config->api->{$name} ?? null) : self::$config->api;
    }

    public static function has(string $section, ?string $row = null): bool {
        if ($row === null) {
            return isset(self::$config->{$section});
        }
        return isset(self::$config->{$section}->{$row});
    }

    public static function set(string $section, string $row, mixed $value): void {
        if (self::$config === null) {
            self::$config = new stdClass();
        }
        if (!isset(self::$config->{$section})) {
            self::$config->{$section} = new stdClass();
        }
        self::$config->{$section}->{$row} = $value;
    }

}
