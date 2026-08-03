<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Http;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Stateless helpers to inspect a PSR-7 server request (replaces the old
 * RequestTrait bolted onto request subclasses).
 */
final class ServerRequestHelper {
    public static function isXhr(ServerRequestInterface $request): bool {
        return strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest';
    }

    public static function isSecure(ServerRequestInterface $request): bool {
        $server = $request->getServerParams();
        return (!empty($server['HTTPS']) && strtolower((string) $server['HTTPS']) !== 'off')
                || ((int) ($server['SERVER_PORT'] ?? 0)) === 443
                || $request->getUri()->getScheme() === 'https';
    }

    public static function allowsCache(ServerRequestInterface $request): bool {
        $header = $request->getHeaderLine('Cache-Control');
        return $header === '' || !str_contains($header, 'no-cache');
    }
}
