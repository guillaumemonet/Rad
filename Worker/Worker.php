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

namespace Rad\Worker;

/**
 * Description of Worker
 *
 * @author Guillaume Monet
 */
abstract class Worker {

    public function __construct() {
        while (true) {
            msg_receive($queue, $desiredmsgtype, $msgtype, $maxsize, &$message);
            //Create new worker thread to perform the message
        }
    }

}
