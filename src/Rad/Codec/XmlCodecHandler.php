<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Codec;

use ErrorException;

/**
 * Description of Xml_CodecHandler
 *
 * @author guillaume
 */
class XmlCodecHandler implements CodecInterface {

    public function __toString() {
        return "XML encode/decode";
    }

    public function deserialize(string $string) {
        throw new CodecException("Not supported yet!");
    }

    public function getMimeTypes(): array {
        return ['xml'];
    }

    public function serialize($object): string {
        $array = get_object_vars($object);
        $xml   = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><data />");
        foreach ($array as $k => $v) {
            error_log($k . ' ' . $v);
            $xml->addChild($k, $v);
        }
        return $xml->asXML();
    }

    public function sign($datas, $secret) {
        
    }

}
