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

namespace Rad\Log;

use Rad\Config\Config;

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
