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
abstract class Worker {

    protected $queue;
    protected $desiredmsgtype;
    protected $maxsize;

    public function __construct($queue, $desiredmsgtype, $maxsize) {
        $this->queue          = $queue;
        $this->desiredmsgtype = $desiredmsgtype;
        $this->maxsize        = $maxsize;
    }

    public function run() {
        $ip = msg_get_queue($this->queue);
        while (true) {
            $message     = null;
            $messageType = null;
            msg_receive($ip, $this->desiredmsgtype, $messageType, $this->maxsize, $message, true);
            $this->event($messageType, $message);
        }
    }

    abstract protected function event(string $messageType, $message);
}
