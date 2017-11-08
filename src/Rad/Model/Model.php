<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Rad\Model;

use JsonSerializable;
use Rad\Config\Config;
use stdClass;

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
     * @return mixed
     */
    public final static function hydrate($object) {
        if (is_object($object)) {
            $model = self::createObject($object);
            self::buildObjectContent($object, $model);
            return $model;
        } else if (is_array($object)) {
            $array = array();
            self::buildArrayContent($object, $array);
            return $array;
        } else {
            return $object;
        }
    }

    /**
     * 
     * @param type $object
     * @return mixed
     */
    private static function createObject($object) {
        $model = new stdClass();
        if (isset($object->resource_name)) {
            $className = $object->resource_namespace . "\\" . $object->resource_name;
            $model = new $className();
        }
        return $model;
    }

    /**
     * 
     * @param type $object
     * @param type $model
     */
    private static function buildObjectContent($object, $model) {
        array_walk($object, function($val, $key) use ($model) {
            $model->$key = self::hydrate($val);
        });
    }

    /**
     * 
     * @param type $object
     * @param array $array
     */
    private static function buildArrayContent($object, array &$array) {
        array_walk($object, function($val, $key) use (&$array) {
            $array[$key] = self::hydrate($val);
        });
    }

}
