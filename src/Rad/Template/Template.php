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

namespace Rad\Template;

use ErrorException;
use Rad\Config\Config;

final class Template {

    private static $templateHandlers = array();

    private function __construct() {
        
    }

    /**
     * 
     * @param string $name
     * @param TemplateInterface $template
     */
    public static function addHandler(string $name, TemplateInterface $template) {
        self::$templateHandlers[$name] = $template;
    }

    /**
     * 
     * @return TemplateInterface
     * @throws ErrorException
     */
    public static function getHandler(string $handlerType = null): TemplateInterface {
        if ($handlerType === null) {
            $handlerType = (string) Config::get("template", "type");
        }
        if (!isset(self::$templateHandlers[$handlerType])) {
            try {
                $className = __NAMESPACE__ . "\\" . ucfirst($handlerType) . "_TemplateHandler";
                self::$templateHandlers[$handlerType] = new $className();
            } catch (ErrorException $ex) {
                throw new ErrorException($handlerType . " Template Handler not found");
            }
        }
        return self::$templateHandlers[$handlerType];
    }

}
