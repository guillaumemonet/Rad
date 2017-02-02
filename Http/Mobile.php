<?php

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

namespace Rad\Http;

use Mobile_Detect;

/**
 * Description of Mobile
 *
 * @author Guillaume
 */
abstract class Mobile {

    private static $mobile_detect = null;

    /**
     * Return if current browser is on mobile
     * @return boolean
     */
    public static function isMobile() {
        if (self::$mobile_detect == null) {
            self::$mobile_dectect = new Mobile_Detect();
        }
        return self::$mobile_detect->isMobile();
    }

    /**
     * Return if current browser is on tablet 
     * @return type
     */
    public static function isTablet() {
        if (self::$mobile_detect == null) {
            self::$mobile_dectect = new Mobile_Detect();
        }
        return self::$mobile_detect->isTablet();
    }

}
