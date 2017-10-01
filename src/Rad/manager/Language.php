<?php

namespace Rad\manager;

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

/**
 * Description of Language
 *
 * @author Guillaume Monet
 */
final class Language {

    /**
     * 
     * @param string $default
     * @return string
     */
    public static function getClientLanguage($default) {
	if (in_array($default, Config::get("language", "language"))) {
	    return $default;
	}

	if (isset(Request::getHeader('HTTP_ACCEPT_LANGUAGE'))) {
	    $langs = explode(',', Request::getHeader('HTTP_ACCEPT_LANGUAGE'));
	    foreach ($langs as $value) {
		$choice = substr($value, 0, 2);
		if (in_array($choice, Config::get("language", "language"))) {
		    return $choice;
		}
	    }
	}
	return $default;
    }

    /**
     * 
     * @param string $string
     * @param string $iso2lang
     * @return string
     */
    public static function translate($string, $iso2lang) {
	include(Config::getRootPath() . DIRECTORY_SEPARATOR . "lang" . DIRECTORY_SEPARATOR . "lang_" . substr(strtolower($iso2lang), 0, 2) . ".php");
	if (isset($lang[$string])) {
	    return $lang[$string];
	} else {
	    Log::getLogger()->warning("No translatation found for \"\".$string.\" in " . $iso2lang);
	    return $string;
	}
    }

}
