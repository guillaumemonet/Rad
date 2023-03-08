<?php

/*
 * The MIT License
 *
 * Copyright 2023 Guillaume Monet.
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

use Exception;
use Rad\Config\Config;

/**
 * Description of OpenSSLEncryption
 *
 * @author Guillaume Monet
 */
class OpenSSLEncryption implements EncryptionInterface {

    private $method = 'aes-256-ctr';

    public function __construct() {
        if (isset(Config::getServiceConfig("encrypt", "openssl")->config->method)) {
            $this->method = Config::getServiceConfig("encrypt", "openssl")->config->method;
        }
    }

    /**
     * Encrypts
     * 
     * @param string $message - plaintext message
     * @param string $key - encryption key (raw binary expected)
     * @param boolean $encode - set to TRUE to return a base64-encoded 
     * @return string (raw binary)
     */
    public function encrypt($datas) {
        $key       = Config::getConfig()->api->token;
        $nonceSize = openssl_cipher_iv_length($this->method);
        $nonce     = openssl_random_pseudo_bytes($nonceSize);

        $ciphertext = openssl_encrypt(
                $datas,
                $this->method,
                $key,
                OPENSSL_RAW_DATA,
                $nonce
        );
        return base64_encode($nonce . $ciphertext);
    }

    /**
     * Decrypts a message
     * 
     * @param string $message - ciphertext message
     * @param string $key - encryption key (raw binary expected)
     * @param boolean $encoded - are we expecting an encoded string?
     * @return string
     */
    public function decrypt($datas) {
        $datas = base64_decode($datas, true);
        if ($datas === false) {
            throw new Exception('Encryption failure');
        }
        $key = Config::getConfig()->api->token;

        $nonceSize  = openssl_cipher_iv_length($this->method);
        $nonce      = mb_substr($datas, 0, $nonceSize, '8bit');
        $ciphertext = mb_substr($datas, $nonceSize, null, '8bit');

        $plaintext = openssl_decrypt(
                $ciphertext,
                $this->method,
                $key,
                OPENSSL_RAW_DATA,
                $nonce
        );

        return $plaintext;
    }

}
