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

namespace Rad\Codec;

use ErrorException;
use Rad\Codec\CodecInterface;
use Rad\Config\Config;

/**
 * Description of Codec
 *
 * @author guillaume
 */
abstract class Codec {

    private static $codecHandlers = array();

    private function __construct() {
        
    }

    /**
     * 
     * @param string $type
     * @param CodecInterface $codecInterface
     */
    public static function addHandler(CodecInterface $codecInterface) {
        foreach ($codecInterface->getMimeTypes() as $type) {
            self::$codecHandlers[$type] = $codecInterface;
        }
    }

    /**
     * 
     * @param string $handlerType
     * @return CodecInterface
     * @throws ErrorException
     */
    public static function getHandler(string $handlerType = null): CodecInterface {
        if ($handlerType === null) {
            $handlerType = (string) Config::get("codec", "default");
        }
        if (!isset(self::$codecHandlers[$handlerType])) {
            try {
                $className = __NAMESPACE__ . "\\" . ucfirst($handlerType) . "_CodecHandler";
                self::$codecHandlers[$handlerType] = file_exists(__DIR__ . '/' . ucfirst($handlerType) . "_CodecHandler.php") ? new $className() : new Default_CodecHandler();
            } catch (ErrorException $ex) {
                throw new ErrorException($handlerType . " Cache Handler not found");
            }
        }
        return self::$codecHandlers[$handlerType];
    }

}
