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

use ErrorException;
use Psr\Log\AbstractLogger;
use Rad\Config\Config;

/**
 * Description of Log
 *
 * @author Guillaume Monet
 */
abstract class Log {

    private static $logHandlers;

    private function __construct() {
        
    }

    /**
     * 
     * @param string $type
     * @param AbstractLogger $logger
     */
    public static function addHandler(string $type, AbstractLogger $logger) {
        self::$logHandlers[$type] = $logger;
    }

    /**
     * 
     * @param string $handlerType
     * @return AbstractLogger
     * @throws ErrorException
     */
    public static function getHandler(string $handlerType = null): AbstractLogger {
        if ($handlerType === null) {
            $handlerType = Config::get("log", "type") !== null ? Config::get("log", "type") : "default";
        }
        if (!isset(self::$logHandlers[$handlerType])) {
            try {
                $className = __NAMESPACE__ . "\\" . ucfirst($handlerType) . "_LogHandler";
                self::$logHandlers[$handlerType] = file_exists(ucfirst($handlerType) . "_LogHandler.php") ? new $className() : new Default_LogHandler();
            } catch (ErrorException $ex) {
                throw new ErrorException($handlerType . " Log Handler not found");
            }
        }
        return self::$logHandlers[$handlerType];
    }

}
