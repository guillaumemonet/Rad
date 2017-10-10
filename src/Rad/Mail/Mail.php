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

namespace Rad\Mail;

use ErrorException;
use Rad\Config\Config;
use Rad\Database\DatabaseAdapter;
use Rad\Database\DatabaseInterface;

abstract class Mail {

    /**
     * @var DatabaseAdapter
     */
    private static $mailHandlers = array();

    private function __construct() {
        
    }

    /**
     * 
     * @param string $type
     * @param MailInterface $mailInterface
     */
    public static function addHandler(string $type, MailInterface $mailInterface) {
        self::$mailHandlers[$type] = $mailInterface;
    }

    /**
     * 
     * @param string $handlerType
     * @return MailInterface
     * @throws ErrorException
     */
    public static function getHandler(string $handlerType = null): MailInterface {
        if ($handlerType === null) {
            $handlerType = (string) Config::get("mail", "type");
        }
        if (!isset(self::$mailHandlers[$handlerType])) {
            try {
                $className = __NAMESPACE__ . "\\" . ucfirst($handlerType) . "_MailHandler";
                self::$mailHandlers[$handlerType] = new $className();
            } catch (ErrorException $ex) {
                throw new ErrorException($handlerType . " Cache Handler not found");
            }
        }
        return self::$mailHandlers[$handlerType];
    }

}

?>
