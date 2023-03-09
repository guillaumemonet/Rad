<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Codec;

/**
 * Description of DefaultCodec
 *
 * @author guillaume
 */
class NoCodecHandler implements CodecInterface {

    public function deserialize(string $string) {
        return $string;
    }

    public function getMimeTypes(): array {
        return ["html", "txt", "plain"];
    }

    public function serialize($object): string {
        return "".$object;
    }

    public function __toString() {
        return "Default Codec Handler";
    }

    public function sign($datas, $secret) {
        return Encryption::sign($datas, $secret);
    }

}
