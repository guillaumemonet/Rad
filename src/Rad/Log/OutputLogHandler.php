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
use Stringable;

/**
 * Default File Logger
 *
 * @author guillaume
 */
class OutputLogHandler extends AbstractLogger {
    public function log($level, string|Stringable $message, array $context = []): void {
        $config = Config::getServiceConfig('log', 'output')->config;
        if ($config->enabled == 1 && $config->{$level} == 1) {
            error_log($this->logFormat(strtoupper((string) $level), (string) $message));
        }
    }

    private function logFormat(string $type, string $message): string {
        return sprintf('[%-9s] %s', $type, $message);
    }

}
