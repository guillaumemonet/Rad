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
     * @param int $queue
     * @param int|MessageType $messageType
     * @param mixed $message
     */
    public static function sendMessage(int $queue, int|MessageType $messageType, mixed $message): bool {
        $type = $messageType instanceof MessageType ? $messageType->value : $messageType;
        $ip   = msg_get_queue($queue);
        return msg_send($ip, $type, $message, true);
    }

}
