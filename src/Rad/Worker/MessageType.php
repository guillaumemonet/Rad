<?php

declare(strict_types=1);

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Worker;

/**
 * Type of a System V message queue message (mtype, must be a positive integer).
 *
 * Extend the cases to model your application's message kinds; the backing value
 * is what {@see Orderer} sends on the queue.
 */
enum MessageType: int {
    case DEFAULT = 1;
    case COMMAND = 2;
    case EVENT   = 3;
}
