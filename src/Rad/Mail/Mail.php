<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Mail;

use Rad\Service\Service;

final class Mail extends Service {

    public static function addHandler(string $handlerType, $handler) {
        static::getInstance()->addServiceHandler($handlerType, $handler);
    }

    public static function getHandler(string $handlerType = null): MailInterface {
        return static::getInstance()->getServiceHandler($handlerType);
    }

    protected function getServiceType(): string {
        return 'mail';
    }

}
