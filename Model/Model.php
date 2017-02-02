<?php

/*
 * The MIT License
 *
 * Copyright 2017 Guillaume Monet.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Rad\Model;

use JsonSerializable;
use Rad\Config\Config;

/**
 * Description of IObject.
 *
 * @author Guillaume Monet
 */
abstract class Model implements JsonSerializable {

    protected $resource_uri;
    protected $resource_name;

    public function getID() {
        return null;
    }

    abstract public function create($force);

    abstract public function read();

    abstract public function update();

    abstract public function delete();

    abstract public function parse($var, $use_cache);

    public function jsonSerialize() {
        $this->generateResourceURI();
        unset($this->original);
        unset($this->password);
        unset($this->secret_key);
        unset($this->trash);
        return (array) $this;
    }

    public function generateResourceURI() {
        $this->resource_uri = Config::get("api", "url") . "v" . Config::get("api", "version") . "/" . $this->resource_name . "/" . $this->getID();
    }

    /**
     * 
     * @param type $object
     * @return IModel
     */
    public final static function hydrate($object) {
        if (is_object($object) && isset($object->resource_name)) {
            $c = "\\model\\" . $object->resource_name;
            $new = new $c;
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
