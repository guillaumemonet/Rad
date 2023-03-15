<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Error\Http;

/**
 * Represents an HTTP 404 error.
 */
class NotFoundException extends HttpException {

    /**
     * 
     * @param string $message
     * @param int $code
     */
    public function __construct(string $message = null, int $code = 404) {
        parent::__construct($message, $code);
    }

}
