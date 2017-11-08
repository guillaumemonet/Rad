<?php

/*
 * The MIT License
 *
 * Copyright 2017 guillaume.
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

use Psr\Log\AbstractLogger;
use Rad\Config\Config;

/**
 * Default File Logger
 *
 * @author guillaume
 */
class File_LogHandler extends AbstractLogger {

    public function log($level, $message, array $context = array()) {
        if (Config::get("log", "enabled") == 1 && Config::get("log", $level) == 1) {
            if (is_array($message)) {
                $message = print_r($message, true);
            }
            Config::get("log", "file") !== null ?
                            error_log($this->logFormat(strtoupper($level), $message), 3, Config::get("log", "file")) :
                            error_log($this->logFormat(strtoupper($level), $message));
        }
    }

    private function logFormat(string $type, string $message) {
        return sprintf("[%-9s] %s", $type, $message);
    }

}
