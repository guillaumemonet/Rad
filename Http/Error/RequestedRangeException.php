<?php

namespace Rad\Http\Error;

/**
 * Represents an HTTP 400 error.
 *
 */
class RequestedRangeException extends HttpException {

    /**
     * Constructor
     *
     * @param string $message If no message is given 'Requested range not satisfiable' will be the message
     * @param string $code Status code, defaults to 416
     */
    public function __construct($message = null, $code = 416) {
        if (empty($message)) {
            $message = 'Requested range not satisfiable';
        }
        parent::__construct($message, $code);
    }

}
