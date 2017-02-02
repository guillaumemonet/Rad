<?php

namespace Rad\Log;

use Rad\Config\Config;

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

/**
 * Description of Log
 *
 * @author Guillaume Monet
 */
abstract class Log {

    private function __construct() {
        
    }

    /**
     * Error Message
     * @param String $message
     */
    public static function error(string $message) {
        self::printLog(self::logFormat("ERROR", $message));
    }

    /**
     * Debug Message
     * @param string $message
     */
    public static function debug(string $message) {
        if (Config::get("log", "debug") == 1) {
            self::printLog(self::logFormat("DEBUG", $message));
        }
    }

    /**
     * Warning Message
     * @param string $message
     */
    public static function warning(string $message) {
        if (Config::get("log", "warning") == 1) {
            self::printLog(self::logFormat("WARNING", $message));
        }
    }

    /**
     * Info Message
     * @param String $message
     */
    public static function info(string $message) {
        if (Config::get("log", "info") == 1) {
            self::printLog(self::logFormat("INFO", $message));
        }
    }

    /**
     * Notice Message
     * @param String $message
     */
    public static function notice(string $message) {
        if (Config::get("log", "notice") == 1) {
            self::printLog(self::logFormat("NOTICE", $message));
        }
    }

    /**
     * 
     * @param type $type
     * @param type $message
     * @return type
     */
    private static function logFormat(string $type, string $message) {
        return sprintf("[%-7s] %s", $type, $message);
    }

    private static function printLog(string $message) {
        if (Config::get("log", "enabled") == 1) {
            if (Config::get("log", "file") !== null) {
                error_log($message . "\n", 3, Config::get("log", "file"));
            } else {
                error_log($message . "\n");
            }
        }
    }

}

?>
