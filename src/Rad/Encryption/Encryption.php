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

namespace Rad\Encryption;

use Rad\Config\Config;

/**
 * Description of Encryption
 *
 * @author Guillaume Monet
 */
abstract class Encryption {

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
     * Sign current datas
     * @param string $data
     * @param string $secret
     * @return string
     */
    public static function sign(string $data, string $secret): string {
        return base64_encode(hash_hmac('md5', $data, $secret, true));
    }

    /**
     * 
     * @param string $data
     * @return string
     */
    public static function hashMd5(string $data): string {
        return hash("md5", $data);
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
