<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Encryption;

use Rad\Config\Config;
use Rad\Service\Service;

/**
 * Description of Encryption
 *
 * @author Guillaume Monet
 */
final class Encryption extends Service {

    public static function addHandler(string $handlerType, $handler) {
        static::getInstance()->addServiceHandler($handlerType, $handler);
    }

    public static function getHandler(string $handlerType = null): Encryption {
        return static::getInstance()->getServiceHandler($handlerType);
    }

    protected function getServiceType(): string {
        return 'encrypt';
    }

    /**
     * 
     * @param string $data
     * @return string
     */
    public static function hashMd5(string $data): string {
        $token = Config::getApiConfig('token');
        return hash("md5", $token . $data);
    }

    /**
     * Generate secure token.
     *
     * @return string
     */
    public static function generateToken($size = 8): string {
        return bin2hex(openssl_random_pseudo_bytes($size));
    }

    /**
     * Convert to mysql password format
     * @param string $input
     * @return string
     */
    public static function password($input): string {
        return "*" . strtoupper(sha1(sha1($input, true)));
    }

}
