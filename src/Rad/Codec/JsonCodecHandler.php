<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Codec;

use Rad\Error\CodecException;

/**
 * Description of JsonCodec
 *
 * @author guillaume
 */
class JsonCodecHandler implements CodecInterface {

    public function __toString() {
        return "Json encode/decode";
    }

    public function deserialize(string $string) {
        $ret = json_decode($string);
        if (json_last_error() > 0) {
            throw new CodecException("Error during json_decode", json_last_error_msg());
        }
        return $ret;
    }

    public function serialize($object): string {
        $ret = json_encode((array) $object);
        if (json_last_error() > 0) {
            throw new CodecException("Error during json_encode", json_last_error_msg());
        }
        return $ret;
    }

    public function getMimeTypes(): array {
        return ['json'];
    }

    public function sign($datas, $secret) {
        
    }

}
