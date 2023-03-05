<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Cookie;

use Rad\Service\Service;

/**
 * Description of Cookie
 *
 * @author guillaume
 */
final class Cookie extends Service {

    public static function addHandler(string $handlerType, $handler) {
        static::getInstance()->addServiceHandler($handlerType, $handler);
    }

    public static function getHandler(string $handlerType = null): CookieInterface {
        return static::getInstance()->getServiceHandler($handlerType);
    }

    protected function getServiceType(): string {
        return 'cookie';
    }

}
