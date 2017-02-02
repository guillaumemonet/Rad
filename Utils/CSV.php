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

namespace Rad\Utils;

/**
 * Description of CSVReader
 *
 * @author Guillaume Monet
 */
final class CSV{

    private $filename = null;
    private $hasHeader = null;
    private $separator = ";";

    public function __construct($filename, $separator = ";", $hasHeader = false) {
        $this->filename = $filename;
        $this->hasHeader = $hasHeader;
        $this->separator = $separator;
    }

    public function read() {
        $all_rows = array();
        $header = null;
        $file = fopen($this->filename, 'r+');
        while ($row = fgetcsv($file)) {
            if ($header === null) {
                $header = $row;
                continue;
            }
            $all_rows[] = array_combine($header, $row);
        }
        return $all_rows;
    }

}
