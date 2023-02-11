<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Session;

/**
 * Description of PHPSessionHandler
 *
 * @author guillaume
 */
class PHPSessionHandler implements SessionInterface {

    public function get(string $index) {
        return $this->has($index) ? $_SESSION[$index] : null;
    }

    public function set(string $index, $value) {
        $_SESSION[$index] = $value;
    }

    public function has(string $index): bool {
        return isset($_SESSION[$index]);
    }

    public function start() {
        session_start();
    }

    public function end() {
        session_write_close();
    }

}
