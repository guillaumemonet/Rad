<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Codec;

use Rad\Http\HttpHeaders;
use Rad\Service\Service;
use Rad\Utils\Mime;

/**
 * Description of Codec
 *
 * @author guillaume
 */
final class Codec extends Service {

    public static function addHandler(string $handlerType, $handler) {
        static::getInstance()->addServiceHandler($handlerType, $handler);
    }

    public static function getHandler(string $handlerType = null): CodecInterface {
        return static::getInstance()->getServiceHandler($handlerType);
    }

    protected function getServiceType(): string {
        return 'codec';
    }

    public static function matchCodec($acceptHeader) {
        $mimeArray      = HttpHeaders::parseAccepted(implode(',', $acceptHeader));
        $availableCodec = array_keys(Codec::getInstance()->services);
        foreach ($mimeArray as $mime => $weight) {
            $shortMime = current(Mime::getMimeTypesFromLong($mime));
            if (in_array($shortMime, $availableCodec)) {
                return $shortMime;
            }
        }
        return Codec::getInstance()->default;
    }

}
