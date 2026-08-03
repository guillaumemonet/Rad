<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Http\Exception;

use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

/**
 * Thrown when a request cannot be sent due to a network/transport failure.
 */
final class NetworkException extends RuntimeException implements NetworkExceptionInterface {
    public function __construct(private RequestInterface $request, string $message) {
        parent::__construct($message);
    }

    public function getRequest(): RequestInterface {
        return $this->request;
    }
}
