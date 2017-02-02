<?php

namespace Rad\Serializer;

use ErrorException;

/*
 * Copyright (C) 2017 Guillaume Monet
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
 * Description of Serializer
 *
 * @author Guillaume Monet
 */
class Serializer {

    private $serializers = array();

    public function __construct() {
        
    }

    public function addSerializer(array $mime_types, ISerializer $serializer) {
        foreach ($mime_types as $mime) {
            $this->parsers[$mime] = $serializer;
        }
    }

    public function serialize($mime_type, $datas) {
        if (isset($this->serializers[$mime_type])) {
            return call_user_func_array([$this->serializers[$mime_type], 'serialize'], [$datas]);
        } else {
            throw new ErrorException("No parser found for " . $mime_type);
        }
    }

}
