<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Worker;

/**
 * Description of Worker
 *
 * @author Guillaume Monet
 */
class Orderer {

    /**
     * 
     * @param int $queue
     * @param int $messageType
     * @param mixed $message
     */
    public static function sendMessage(int $queue, int $messageType, $message): bool {
        $ip = msg_get_queue($queue);
        return msg_send($ip, $messageType, $message, true);
    }

}
