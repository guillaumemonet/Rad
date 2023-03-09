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
class OutputLogHandler extends AbstractLogger {

    public function log($level, $message, array $context = []) {
        $config = Config::getServiceConfig("log", "output")->config;
        if ($config->enabled == 1 && $config->{$level} == 1) {
            if (is_array($message)) {
                $message = print_r($message, true);
            }
            error_log($this->logFormat(strtoupper($level), $message));
        }
    }

    private function logFormat(string $type, string $message) {
        return sprintf("[%-9s] %s", $type, $message);
    }

}
