<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Model;

use JsonSerializable;
use Rad\Utils\StringUtils;
use ReflectionClass;

/**
 * Description of IObject.
 *
 * @author Guillaume Monet
 * 
 */
class Model implements JsonSerializable {

    public $resource_uri;
    public $resource_name;
    public $resource_namespace;

    public function __construct() {
        $this->generateResource();
    }

    public function getId() {
        return null;
    }

    public function getResourceName() {
        return $this->resource_name;
    }

    public function getResourceNamespace() {
        return $this->resource_namespace;
    }

    public function getResourceURI() {
        return $this->resource_uri;
    }

    public function jsonSerialize() {
        $this->generateResource();
        return $this;
    }

    public function hydrate(array $datas) {
        array_walk($datas, function ($val, $key) {
            $this->{$key} = $val;
        });
    }

    public function generateResource() {
        $reflex                   = new ReflectionClass($this);
        $this->resource_name      = $reflex->getShortName();
        $this->resource_namespace = $reflex->getName();
        $this->resource_uri       = '/' . StringUtils::slugify($this->resource_name) . '/' . $this->getId() ?? '';
    }

}
