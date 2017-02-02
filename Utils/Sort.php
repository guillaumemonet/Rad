<?php

namespace Rad\Utils;

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
 * Description of Sort
 *
 * @author Guillaume Monet
 */
final class Sort {

    private $order;
    private $order_by;

    const DESC = -1;
    const ASC = 1;

    public function __construct($order_by, $order = Sort::ASC) {
        $this->order = $order;
        $this->order_by = $order_by;
    }

    public static function sortBy(array &$array, $order_by, $order = Sort::ASC) {
        $sort = new Sort($order_by, $order);
        return uasort($array, array($sort, "sort"));
    }

    public function sort($a, $b) {
        $field = $this->order_by;
        if ($a->$field == $b->$field) {
            return 0;
        }
        return $this->order * (($a->$field < $b->$field) ? -1 : 1);
    }

}
