<?php

namespace Rad\Controller;

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

/*
 * Description of IController
 *
 * @author Guillaume Monet
 */

abstract class IController {

    public static $type = array("CREATE" => 10001, "UPDATE" => 10002, "DELETE" => 10003);

    /**
     * Call on specific event to provide webhook to customer
     * @param type $resource_uri
     * @param type $type
     * @param type $marketplace
     */
    public static function callWebhook($resource_uri, $type, $marketplace) {
        if (in_array($type, self::$type)) {
            $queue = msg_get_queue(self::$type[$type]);
            msg_send($queue, uniqid(), $message);
        }
    }

    /**
     * Return mathing objects with get filter
     * @param array $datas
     * @param array $get
     * @return array
     */
    public static function matchFilter(array $datas, array $get) {
        if (sizeof($get) > 0) {
            $match = array();
            foreach ($datas as $obj) {
                if (array_intersect_assoc((array) $obj, $get) == $get) {
                    $match[] = $obj;
                }
            }
            return $match;
        } else {
            return $datas;
        }
    }

}
