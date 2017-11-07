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

namespace Rad\Utils;

use Rad\Cache\QuickCache;

/**
 * Description of Time
 *
 * @author Guillaume
 */
abstract class Time {

    private $counter = null;

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

    public static function startCounter() {
        self::$counter = self::get_microtime();
    }

    public static function endCounter() {
        if (self::$counter !== null) {
            return self::get_microtime() - $counter;
        } else {
            return null;
        }
    }

    /**
     * Return french holiday
     * @param int $unixTimeStamp
     * @return bool
     */
    public static function isFrenchHoliday($unixTimeStamp = null) {
        $date = strtotime(date('m/d/Y', $unixTimeStamp == null ? time() : $unixTimeStamp));
        $year = date('Y', $date);
        $holidays = QuickCache::getDatas($year);
        if ($holidays == null) {
            $easterDate = easter_date($year) + 3 * 3600;
            $easterDay = date('j', $easterDate);
            $easterMonth = date('n', $easterDate);
            $easterYear = date('Y', $easterDate);
            $holidays = array(
                // Dates fixes
                mktime(0, 0, 0, 1, 1, $year), // 1er janvier
                mktime(0, 0, 0, 5, 1, $year), // Fête du travail
                mktime(0, 0, 0, 5, 8, $year), // Victoire des alliés
                mktime(0, 0, 0, 7, 14, $year), // Fête nationale
                mktime(0, 0, 0, 8, 15, $year), // Assomption
                mktime(0, 0, 0, 11, 1, $year), // Toussaint
                mktime(0, 0, 0, 11, 11, $year), // Armistice
                mktime(0, 0, 0, 12, 25, $year), // Noel
                // Dates variables
                //Lundi de paques
                mktime(0, 0, 0, $easterMonth, $easterDay + 1, $easterYear),
                //Jeudi de l'ascension
                mktime(0, 0, 0, $easterMonth, $easterDay + 39, $easterYear),
                //Lundi pentecote
                mktime(0, 0, 0, $easterMonth, $easterDay + 50, $easterYear),
            );
            QuickCache::setDatas($year, $holidays);
        }
        return in_array($date, $holidays);
    }

}
