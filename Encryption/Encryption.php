<?php

/*
 * Copyright (C) 2016 Guillaume Monet
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Rad\Encryption;

use Rad\Config\Config;

/**
 * Description of Encryption
 *
 * @author Guillaume Monet
 */
final class Encryption {

    private function __construct() {
        
    }

    private static $cipher = MCRYPT_RIJNDAEL_128;
    private static $mode = 'cbc';

    /**
     * Encrypt Datas
     * @param string $data
     * @return string
     */
    public static function crypt(string $data) {
        $keyHash = md5(Config::get("encrypt", "key"));
        $key = substr($keyHash, 0, mcrypt_get_key_size(self::$cipher, self::$mode));
        $iv = substr($keyHash, 0, mcrypt_get_block_size(self::$cipher, self::$mode));
        return base64_encode(mcrypt_encrypt(self::$cipher, $key, $data, self::$mode, $iv));
    }

    /**
     * Decrypt Datas
     * @param string $data
     * @return string
     */
    public static function decrypt(string $data) {
        $keyHash = md5(Config::get("encrypt", "key"));
        $key = substr($keyHash, 0, mcrypt_get_key_size(self::$cipher, self::$mode));
        $iv = substr($keyHash, 0, mcrypt_get_block_size(self::$cipher, self::$mode));
        return mcrypt_decrypt(self::$cipher, $key, base64_decode($data), self::$mode, $iv);
    }

    /**
     * 
     * @param array $data
     * @param string $secret
     * @return type
     */
    public static function sign(string $data, string $secret) {
        return base64_encode(hash_hmac('md5', $data, $secret, true));
    }

    /**
     * Generate secure token.
     *
     * @return string
     */
    public static function generateToken($size = 8) {
        return bin2hex(openssl_random_pseudo_bytes($size));
    }

    /**
     * Convert to mysql password format
     * @param string $input
     * @return string
     */
    public static function password($input) {
        return "*" . strtoupper(sha1(sha1($input, true)));
    }

}
