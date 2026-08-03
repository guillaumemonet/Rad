<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * Emits a PSR-7 response to the SAPI (status line, headers, body).
 */
class Emitter {
    public function emit(ResponseInterface $response): void {
        if (!headers_sent()) {
            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header(sprintf('%s: %s', $name, $value), false);
                }
            }
            $reason = $response->getReasonPhrase();
            header(sprintf(
                'HTTP/%s %d%s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $reason !== '' ? ' ' . $reason : ''
            ), true, $response->getStatusCode());
        }
        echo $response->getBody();
    }
}
