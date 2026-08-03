<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Http;

use GuzzleHttp\Psr7\ServerRequest as GuzzleServerRequest;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Builds a PSR-7 ServerRequest from the PHP superglobals.
 *
 * Isolates the concrete implementation used to read the current request.
 */
final class ServerRequestFactory {
    public static function fromGlobals(): ServerRequestInterface {
        return GuzzleServerRequest::fromGlobals();
    }
}
