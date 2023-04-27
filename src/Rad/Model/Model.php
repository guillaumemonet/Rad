<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Model;

use JsonSerializable;

/**
 * Description of IObject.
 *
 * @author Guillaume Monet
 * 
 */
abstract class Model implements JsonSerializable {

    public function jsonSerialize() {
        return $this;
    }

    public function hydrate(array $datas) {
        array_walk($datas, function ($val, $key) {
            $this->{$key} = $val;
        });
    }

}
