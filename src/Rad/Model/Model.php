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
    public ?int $id;

    public function __construct() {
        
    }

    public function getId() {
        return $this->id;
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

    public static function getSQLFilters($filters) {
        $filters['trash'] = 0;
        $callingClass     = get_called_class();
        $res_filters      = array_intersect_key($filters, $callingClass::$tableFormat);
        ksort($res_filters); // Tri par clé
        $md5              = md5(serialize($res_filters));
        $bind_array       = [];
        $conditions       = array_map(function ($cle, $valeur) use (&$bind_array) {
            if ($valeur == 'null') {
                return '`' . $cle . '` is null';
            } else {
                $bind_array[':' . $cle] = $valeur;
                return '`' . $cle . "` = :" . $cle;
            }
        }, array_keys($res_filters), $res_filters);
        $chaine = implode(" AND ", $conditions);
        if (strlen($chaine) > 0) {
            $chaine = ' AND ' . $chaine;
        }
        return ['md5' => $md5, 'bind' => $bind_array, 'sql' => $chaine];
    }

}
