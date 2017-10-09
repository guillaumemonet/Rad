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

namespace Rad\bin;

use Rad\bin\scripts\GenerateBase;

require(__DIR__ . "/../../vendor/autoload.php");

final class console {

    public function __construct($argv) {
        $class = $argv[0];
        $action = $argv[1];
        $verb = $argv[2];
        $opt = $argv[3];
        switch ($action) {
            case "create":
                switch ($verb) {
                    case "skel":
                        break;
                    default:
                        echo "Nothing to create";
                }
                break;
            case "build":
                switch ($verb) {
                    case "classes";
                        $generate = new GenerateBase();
                        $generate->generate();
                        break;
                    default:
                        echo "Nothing to build";
                }
                break;
            case "worker":
                switch ($verb) {
                    case "launch":
                        break;
                    case "kill":
                        break;
                    case "status":
                        break;
                }
                break;
        }
    }

}

new console($argv);
