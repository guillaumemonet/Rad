<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @package rad-framework
 */

namespace Rad\Observer;

/**
 * Description of Observable
 *
 */
abstract class Observable {

    private $observers = [];

    public function attach(Observer $observer) {
        $this->observers[spl_object_hash($observer)] = $observer;
    }

    public function detach(Observer $observer) {
        unset($this->observers[spl_object_hash($observer)]);
    }

    public function notify() {
        array_map(function($observer) {
            $observer->update($this);
        }, $this->observers);
    }

}
