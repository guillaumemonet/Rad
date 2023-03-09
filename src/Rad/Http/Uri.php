<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Http;

/**
 * PSR 7 Compilant UriInterface
 *
 * @author guillaume
 */
use GuzzleHttp\Psr7\Uri as GUri;

class Uri extends GUri {

    /**
     * If the current uri is secured
     * @return bool
     */
    public function isSecure(): bool {
        return in_array($this->getScheme(), ['https', 'sftp']);
    }

    /**
     * Valid if current provided string is an URL
     * @param string $url
     * @return bool
     */
    public static function isURL(string $url): bool {
        return (boolean) !(filter_var($url, FILTER_SANITIZE_URL | FILTER_VALIDATE_URL) === false);
    }

}
