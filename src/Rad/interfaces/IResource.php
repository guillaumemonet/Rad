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

namespace Rad\interfaces;

/**
 * Description of IResource.
 *
 * @author Guillaume Monet
 */
abstract class IResource implements \JsonSerializable {

    /**
     * 
     * @return string
     */
    public function jsonSerialize() {
	$this->generateResourceURI();
	unset($this->original);
	unset($this->password);
	unset($this->secret_key);
	unset($this->trash);
	return (array) $this;
    }

    public function generateResourceURI() {
	$this->resource_uri = $this->resource_name . "/" . $this->id;
    }

    /**
     * 
     * @param type $object
     * @return \Rad\interfaces\c
     */
    public final static function hydrate($object) {
	if (is_object($object) && isset($object->resource_name)) {
	    $c = "\\Rad\\resources\\" . $object->resource_name;
	    $new = new $c;
	    foreach ($object as $key => $val) {
		if (is_object($val)) {
		    $new->$key = self::hydrate($val);
		} else if (is_array($val)) {
		    foreach ($val as $k => $v) {
			$val[$k] = self::hydrate($v);
		    }
		} else {
		    $new->$key = $val;
		}
	    }
	    return $new;
	} else if ($object !== null) {
	    $array = array();
	    foreach ($object as $key => $val) {
		if (is_object($val)) {
		    $array[$key] = self::hydrate($val);
		} else if (is_array($val)) {
		    foreach ($val as $k => $v) {
			$array[$k] = self::hydrate($v);
		    }
		}
	    }
	    return $array;
	} else {
	    return null;
	}
    }

}
