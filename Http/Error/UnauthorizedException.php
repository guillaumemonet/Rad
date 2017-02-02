<?php

namespace Rad\Http\Error;

/**
 * Represents an HTTP 40error.
 */
class UnauthorizedException extends HttpException {

    /**
     * Constructor
     *
     * @param string $message If no message is given 'Unauthorized' will be the message
     * @param string $code Status code, defaults to 401
     */
    public function __construct($message = null, $code = 401) {
        if (empty($message)) {
            $message = 'Unauthorized';
        }
        parent::__construct($message, $code);
    }

}
