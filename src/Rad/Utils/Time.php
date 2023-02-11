<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Utils;

use Rad\Cache\Cache;

/**
 * Description of Time
 *
 * @author Guillaume
 */
abstract class Time {

    /**
     * 
     * @var float
     */
    private static $counter = null;

    private function __construct() {
        
    }

    private function __clone() {
        
    }

    /**
     * Return current microtime.
     *
     * @return float
     */
    public static function get_microtime(): float {
        return microtime(true);
    }

    public static function startCounter() {
        self::$counter = self::get_microtime();
    }

    public static function endCounter(): ?float {
        if (self::$counter !== null) {
            return self::get_microtime() - self::$counter;
        } else {
            return null;
        }
    }

    public static function resetCounter() {
        self::$counter = null;
    }

    /**
     * Return french holiday
     * @param int $unixTimeStamp
     * @return bool
     */
    public static function isFrenchHoliday(int $unixTimeStamp = null): bool {
        $date     = strtotime(date('m/d/Y', $unixTimeStamp == null ? time() : $unixTimeStamp));
        $year     = date('Y', $date);
        $holidays = Cache::getHandler('quick')->get('holiday' . $year);
        if ($holidays == null) {
            $easterDate  = easter_date($year) + 3 * 3600;
            $easterDay   = date('j', $easterDate);
            $easterMonth = date('n', $easterDate);
            $easterYear  = date('Y', $easterDate);
            $holidays    = array(
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
            Cache::getHandler('quick')->set('holiday' . $year, $holidays);
        }
        return in_array($date, $holidays);
    }

}
