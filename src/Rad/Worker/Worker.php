<?php

/*
 * The MIT License
 *
 * Copyright 2017 Guillaume Monet.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
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
        $this->queue = $queue;
        $this->desiredmsgtype = $desiredmsgtype;
        $this->maxsize = $maxsize;
    }

    public function run() {
        $ip = msg_get_queue($this->queue);
        while (true) {
            $message = null;
            $messageType = null;
            msg_receive($ip, $this->desiredmsgtype, $messageType, $this->maxsize, $message, true);
            $this->event($messageType, $message);
        }
    }

    abstract protected function event(string $messageType, $message);
}
