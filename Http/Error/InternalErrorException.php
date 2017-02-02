<?php

namespace Rad\Http\Error;

/**
 * Represents an HTTP 500 error.
 */
class InternalErrorException extends HttpException {

    /**
     * Constructor
     *
     * @param string $message If no message is given 'Internal Server Error' will be the message
     * @param string $code Status code, defaults to 500
     */
    public function __construct($message = null, $code = 500) {
        if (empty($message)) {
            $message = 'Internal Server Error';
        }
        parent::__construct($message, $code);
    }

}
