<?php

namespace Rad\Utils;

/*
 * Copyright (C) 2017 Guillaume
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
 * Description of Time
 *
 * @author Guillaume
 */
abstract class Time {

    private function __construct() {
        
    }

    /**
     * Return current microtime.
     *
     * @return int
     */
    public static function get_microtime() {
        list($tps_usec, $tps_sec) = explode(' ', microtime());

        return (float) $tps_usec + (float) $tps_sec;
    }

}
