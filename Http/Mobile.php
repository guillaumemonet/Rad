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
