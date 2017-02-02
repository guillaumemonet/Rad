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

namespace Rad\utils;

/**
 * Description of Math
 *
 * @author Guillaume Monet
 */
abstract class Math {

    private function __construct() {
        
    }

    /**
     * 
     * @param float $float
     * @param int $precision
     * @return float
     */
    public static function round(float $float, int $precision = 2) {
        return round($float, $precision);
    }

}
