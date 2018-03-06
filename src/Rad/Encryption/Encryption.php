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
