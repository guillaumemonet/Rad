<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Rad\Model;

use JsonSerializable;
use Rad\Config\Config;

/**
 * Description of IObject.
 *
 * @author Guillaume Monet
 * 
 */
abstract class Model extends ModelDAO implements JsonSerializable {

    public $resource_uri;
    public $resource_name;
    public $resource_namespace;

    public function getID() {
        return null;
    }

    public function getResourceName() {
        return $this->resource_name;
    }

    public function getResourceURI() {
        return $this->resource_uri;
    }

    public function jsonSerialize() {
        $this->generateResourceURI();
        return $this;
    }

    public function generateResourceURI() {
        $this->resource_uri = Config::get("api", "url") . "v" . Config::get("api", "version") . "/" . strtolower($this->getResourceName()) . "/" . $this->getID();
    }

    /**
     * 
     * @param type $object
     * @return Model
     */
    public final static function hydrate($object) {
        if (is_object($object) && isset($object->resource_name)) {
            $c = $object->resource_namespace . "\\" . $object->resource_name;
            $className = $c;
            $new = new $className();
            foreach ($object as $key => $val) {
                if (is_object($val)) {
                    $new->$key = self::hydrate($val);
                } else if (is_array($val)) {
                    foreach ($val as $k => $v) {
                        $val[$k] = self::hydrate($v);
                    }
                }
                $new->$key = $val;
            }
            return $new;
        } else if ($object !== null && is_array($object)) {
            $array = array();
            foreach ($object as $key => $val) {
                if (is_object($val)) {
                    $array[$key] = self::hydrate($val);
                } else if (is_array($val)) {
                    foreach ($val as $k => $v) {
                        $array[$k] = self::hydrate($v);
                    }
                }
            }
            return $array;
        } else {
            return null;
        }
    }

}
