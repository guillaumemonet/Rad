<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Codec;

/**
 * Description of CodecInterface
 *
 * @author guillaume
 */
interface CodecInterface {

    public function getMimeTypes(): array;

    public function serialize($object);

    public function deserialize(string $string);

    public function sign($datas, $secret);

    public function __toString();
}
