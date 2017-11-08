<?php

require(__DIR__ . "/../vendor/autoload.php");

use Rad\Api;
use Rad\Config\Config;
use Rad\Controller\Controller;

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

/**
 * Description of TestBase
 *
 * @author guillaume
 */
class App extends Api {

    public function addControllers(): array {
        return array(
            MyController::class
        );
    }

}

class MyController extends Controller {

    /**
     * @api 0
     * @get /^$|^\/$/
     * @produce html
     */
    public function helloWorld() {
        return "<b>Hello World</b>";
    }

}

Config::set("cache", "type", "file");
Config::set("cache", "enabled", 0);
Config::set("log", "type", "file");
Config::set("log", "error", 1);
Config::set("log", "debug", 1);
Config::set("log", "enabled", 1);
$app = new App();
$app->run();
