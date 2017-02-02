<?php

namespace Rad\Http\Error;

use ErrorException;
use JsonSerializable;

class HttpException extends ErrorException implements JsonSerializable {

    public function jsonSerialize() {
        return array("error" => array(
                "code" => $this->code,
                "message" => $this->message,
                "timestamp" => time(),
                "host" => $_SERVER["SERVER_NAME"],
                "method" => $_SERVER["REQUEST_METHOD"],
                "uri" => $_SERVER["REQUEST_URI"]
            )
        );
    }

    public function __toString() {
        return $this->message;
    }

}
