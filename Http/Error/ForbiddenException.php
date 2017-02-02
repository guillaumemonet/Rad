<?php

namespace Rad\Http\Error;
/**
 * Represents an HTTP 403 error.
 */
class ForbiddenException extends HttpException {

    /**
     * Constructor
     *
     * @param string $message If no message is given 'Forbidden' will be the message
     * @param string $code Status code, defaults to 403
     */
    public function __construct($message = null, $code = 403) {
        if (empty($message)) {
            $message = 'Forbidden';
        }
        parent::__construct($message, $code);
    }

}
