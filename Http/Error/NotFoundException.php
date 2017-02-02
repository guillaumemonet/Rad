<?php

namespace Rad\Http\Error;

/**
 * Represents an HTTP 404 error.
 */
class NotFoundException extends HttpException {

    /**
     * Constructor
     *
     * @param string $message If no message is given 'Not Found' will be the message
     * @param string $code Status code, defaults to 404
     */
    public function __construct($message = null, $code = 404) {
        if (empty($message)) {
            $message = 'Not Found';
        }
        parent::__construct($message, $code);
    }

}
