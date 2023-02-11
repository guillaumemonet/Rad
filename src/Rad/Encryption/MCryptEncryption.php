<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Encryption;

use Rad\Config\Config;

/**
 * Description of MCryptEncryption
 *
 * @author guillaume
 */
class MCryptEncryption implements EncryptionInterface {

    private static $cipher = MCRYPT_RIJNDAEL_128;
    private static $mode   = 'cbc';

    /**
     * Encrypt Datas
     * @param string $data
     * @return string
     */
    public function crypt(string $data) {
        $keyHash = md5(Config::getConfig()->encrypt->key);
        $key     = substr($keyHash, 0, mcrypt_get_key_size(self::$cipher, self::$mode));
        $iv      = substr($keyHash, 0, mcrypt_get_block_size(self::$cipher, self::$mode));
        return base64_encode(mcrypt_encrypt(self::$cipher, $key, $data, self::$mode, $iv));
    }

    /**
     * Decrypt Datas
     * @param string $data
     * @return string
     */
    public function decrypt(string $data) {
        $keyHash = md5(Config::getConfig()->encrypt->key);
        $key     = substr($keyHash, 0, mcrypt_get_key_size(self::$cipher, self::$mode));
        $iv      = substr($keyHash, 0, mcrypt_get_block_size(self::$cipher, self::$mode));
        return mcrypt_decrypt(self::$cipher, $key, base64_decode($data), self::$mode, $iv);
    }

    /**
     * Sign current datas
     * @param string $data
     * @param string $secret
     * @return string
     */
    public function sign(string $data, string $secret): string {
        return base64_encode(hash_hmac('md5', $data, $secret, true));
    }

}
