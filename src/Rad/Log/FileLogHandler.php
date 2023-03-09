<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Log;

use Psr\Log\AbstractLogger;
use Rad\Config\Config;

/**
 * Default File Logger
 *
 * @author guillaume
 */
class FileLogHandler extends AbstractLogger {

    public function log($level, $message, array $context = []) {
        $config = Config::getServiceConfig("log", "file")->config;
        if ($config->enabled == 1 && $config->{$level} == 1) {
            if (is_array($message)) {
                $message = print_r($message, true);
            }
            $config->file !== null ?
                            error_log($this->logFormat(strtoupper($level), $message) . "\n", 3, Config::getApiConfig()->install_path . $config->file) :
                            error_log($this->logFormat(strtoupper($level), $message));
        }
    }

    private function logFormat(string $type, string $message) {
        $time = "";
        if (Config::getServiceConfig("log")->displayTime) {
            $time = date("[Y-m-d H:i:s] ");
        }
        return sprintf("%s[%-9s] %s", $time, $type, $message);
    }

}
