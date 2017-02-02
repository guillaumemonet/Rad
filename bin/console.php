<?php

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

namespace console;

use Rad\bin\scripts\generate_class;

require_once(__DIR__ . '/../../Bootstrap.php');

final class console {

    public function __construct($argv) {
        $class = $argv[0];
        $action = $argv[1];
        $verb = $argv[2];
        $opt = $argv[3];
        switch ($action) {
            case "create":
                switch ($verb) {
                    case "app":
                        break;
                    default:
                        echo "Nothing to create";
                }
                break;


            case "build":
                switch ($verb) {
                    case "classes";
                        generate_class::generate();
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
