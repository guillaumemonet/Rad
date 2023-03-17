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
class SerializeCodecHandler implements CodecInterface {

    public function deserialize(string $string) {
        return unserialize($string);
    }

    public function getMimeTypes(): array {
        return ['php'];
    }

    public function serialize($object): string {
        return serialize($object);
    }

    public function __toString() {
        return 'Default PHP serialize/deserialize';
    }

    public function sign($datas, $secret) {
        
    }

}
