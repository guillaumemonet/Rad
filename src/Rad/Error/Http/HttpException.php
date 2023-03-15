<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Error\Http;

use Rad\Error\RadException;
use Rad\Http\StatusCode;

class HttpException extends RadException {

    /**
     * 
     * @param string $message
     * @param int $code
     */
    public function __construct(string $message = null, int $code = 500) {
        if (empty($message)) {
            $message = StatusCode::getMessageForCode($code);
        }
        parent::__construct($message, $code);
    }

    public function jsonSerialize() {
        return array("error" => array(
                "code"      => $this->code,
                "message"   => $this->message,
                "timestamp" => time(),
                "host"      => $_SERVER["SERVER_NAME"],
                "method"    => $_SERVER["REQUEST_METHOD"],
                "uri"       => $_SERVER["REQUEST_URI"]
            )
        );
    }

}
