<?php

namespace Rad\Parser;

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
 * Description of Parser
 *
 * @author Guillaume Monet
 */
class Parser {

    private $parsers = array();

    public function __construct() {
        
    }

    public function addParser(array $mime_types, IParser $parser) {
        foreach ($mime_types as $mime) {
            $this->parsers[$mime] = $parser;
        }
    }

    public function parse($mime_type, $datas) {
        if (isset($this->parsers[$mime_type])) {
            return call_user_func_array([$this->parsers[$mime_type], 'parse'], [$datas]);
        } else {
            throw new ErrorException("No parser found for " . $mime_type);
        }
    }

}
