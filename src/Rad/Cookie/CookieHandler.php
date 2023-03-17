<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Cookie;

use Rad\Config\Config;
use Rad\Encryption\Encryption;

/**
 * Description of PHPSessionHandler
 *
 * @author guillaume
 */
class CookieHandler implements CookieInterface {

    /**
     * 
     * @var array
     */
    private $datas = null;
    private $name  = null;

    public function __construct() {
        $this->name = Config::getServiceConfig('cookie', 'php')->config->name;
        if (isset($_COOKIE[$this->name])) {
            $this->datas = unserialize(Encryption::getHandler()->decrypt($_COOKIE[$this->name]));
        }
        if (!is_array($this->datas)) {
            $this->datas = [];
        }
    }

    public function get(string $index) {
        return $this->has($index) ? $this->datas[$index] : null;
    }

    public function set(string $index, $value) {
        $this->datas[$index] = $value;
    }

    public function has(string $index): bool {
        return isset($this->datas[$index]);
    }

    public function save(): bool {
        return setcookie($this->name, Encryption::getHandler()->encrypt(serialize($this->datas)));
    }

}
