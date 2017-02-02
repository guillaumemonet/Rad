<?php

namespace Rad\Http\Error;

/**
 * Represents an HTTP 412 error.
 */
class ContextNotAllowedException extends HttpException {

    /**
     * Constructor
     *
     * @param string $message If no message is given 'Precondition Failed' will be the message
     * @param string $code Status code, defaults to 412
     */
    public function __construct($message = null, $code = 412) {
        if (empty($message)) {
            $message = 'Precondition Failed';
        }
        parent::__construct($message, $code);
    }

}
