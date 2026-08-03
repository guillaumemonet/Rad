<?php

declare(strict_types=1);

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Security;

use Rad\Error\RadException;

/**
 * Thrown when a JWT is malformed, has an unexpected algorithm, fails signature
 * verification, or is expired / not yet valid.
 */
final class JwtException extends RadException {
    public function __construct(string $message = 'Invalid token', int $code = 401) {
        parent::__construct($message, $code);
    }
}
