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

namespace Rad\manager;

use Rad\interfaces\IManager as IManager;

/**
 * Description of Recherche
 *
 * @author Guillaume Monet
 */
final class Recherche implements IManager {

    /**
     * 
     * @param type $s
     * @param type $t
     * @param type $workspace
     * @return type
     */
    final public static function damlev_algo($s, $t, $workspace = array()) {
	if (strlen($s) > strlen($t)) {
	    $temp = $s;
	    $s = $t;
	    $t = $temp;
	}
	$lenS = strlen($s);
	$lenT = strlen($t);
	$lenS1 = $lenS + 1;
	$lenT1 = $lenT + 1;
	if ($lenT1 == 1) {
	    return $lenS1 - 1;
	}
	if ($lenS1 == 1) {
	    return $lenT1 - 1;
	}
	$dl = $workspace;
	$dlIndex = 0;
	$sPrevIndex = 0;
	$tPrevIndex = 0;
	$rowBefore = 0;
	$min = 0;
	$cost = 0;
	$tmp = 0;
	$tri = $lenS1 + 2;
	$dlIndex = 0;
	for ($tmp = 0; $tmp < $lenT1; $tmp++) {
	    $dl[$dlIndex] = $tmp;
	    $dlIndex += $lenS1;
	}
	for ($sIndex = 0; $sIndex < $lenS; $sIndex++) {
	    $dlIndex = $sIndex + 1;
	    $dl[$dlIndex] = $dlIndex;
	    for ($tIndex = 0; $tIndex < $lenT; $tIndex++) {
		$rowBefore = $dlIndex;
		$dlIndex += $lenS1;
//deletion
		$min = $dl[$rowBefore] + 1;
// insertion
		$tmp = $dl[$dlIndex - 1] + 1;
		if ($tmp < $min) {
		    $min = $tmp;
		}
		$cost = 1;
		if ($s[$sIndex] == $t[$tIndex]) {
		    $cost = 0;
		}
		if ($sIndex > 0 && $tIndex > 0) {
		    if ($s[$sIndex] == $t[$tPrevIndex] && $s[$sPrevIndex] == $t[$tIndex]) {
			$tmp = $dl[$rowBefore - $tri] + $cost;
// transposition
			if ($tmp < $min) {
			    $min = $tmp;
			}
		    }
		}
// substitution
		$tmp = $dl[$rowBefore - 1] + $cost;
		if ($tmp < $min) {
		    $min = $tmp;
		}
		$dl[$dlIndex] = $min;
		$tPrevIndex = $tIndex;
	    }
	    $sPrevIndex = $sIndex;
	}
	return $dl[$dlIndex];
    }

    /**
     * 
     * @param type $search
     * @param type $weight
     * @return type
     */
    final public static function damlev_search($search, $weight = 0) {
	$arrays = array();
	$arrays = explode(" ", $search);
	$ret = array();
	for ($i = 0; $i < count($arrays); $i++) {
	    $ret_words = "";
	    $rech = $arrays[$i];
	    if (strlen($rech) >= 2) {
		$sql = "SELECT keyword,weight FROM recherche.keywords WHERE weight<=" . $weight . " ORDER BY keyword,weight ASC";
		$words = bb::$database->query($sql);
		$lev = -1;
		while ($word = bb::$database->fetch_object($words)) {
		    $levs = damlev_algo($rech, $word->keyword);
		    if ($levs > 0 && $word->weight < 0) {
			$levs += $word->weight;
		    }
		    if ($levs < $lev || $lev == -1) {
			$ret_words = $word->keyword;
			$lev = $levs;
		    }
		}
		$ret[] = $ret_words;
	    }
	}
	return $ret;
    }

}
