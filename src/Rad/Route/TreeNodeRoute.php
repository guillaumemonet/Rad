<?php

/*
 * The MIT License
 *
 * Copyright 2017 guillaume.
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

namespace Rad\Route;

use Rad\Log\Log;

/**
 * Tree For Routing
 *
 * @author guillaume
 */
class TreeNodeRoute {

    /**
     * RegExp of the current left
     * @var string 
     */
    protected $path_chunk;

    /**
     *
     * @var Route 
     */
    protected $route = null;

    /**
     *
     * @var TreeNodeRoute[]
     */
    protected $children = [];

    /**
     * Bubbled args
     * @var array
     */
    protected $regArgs = [];

    public function __construct($path_ckunk = '') {
        $this->path_chunk = $path_ckunk;
    }

    /**
     * 
     * @param TreeNodeRoute $node
     */
    protected function addChild(TreeNodeRoute $node) {
        $this->children[$node->path_chunk] = $node;
    }

    /**
     * 
     * @param array $array
     * @param Route $route
     */
    public function addFromArray(array $array, Route $route) {
        if (count($array) > 0) {
            $current_value = array_shift($array);
            if (!isset($this->children[$current_value])) {
                $newNode             = new TreeNodeRoute($current_value);
                $newNode->path_chunk = $current_value;
                $newNode->addFromArray($array, $route);
                $this->addChild($newNode);
            } else {
                $this->children[$current_value]->addFromArray($array, $route);
            }
        } else {
            $this->route = $route;
        }
    }

    public function getRoute($array) {
        if (count($array) > 0) {
            $matching_nodes = $this->matchRoute(array_shift($array));
            $routes         = array_map(function ($node) use ($array) {
                return $node->getRoute($array);
            }, $matching_nodes);
            return current($routes);
        } else {
            return ($this->route !== null) ? $this->route->setArgs($this->regArgs) : null;
        }
    }

    private function matchRoute($value) {
        return array_filter($this->children, function ($node, $key) use ($value) {
            $match  = [];
            $pmatch = preg_match('/^' . $key . '$/', $value, $match);
            Log::getHandler()->debug('preg_match ' . '/^' . $key . '$/' . ' value :' . $value);
            if ($pmatch) {
                $node->setArgs($this->regArgs);
                $node->addArgs($match);
                return true;
            } else {
                return false;
            }
        }, ARRAY_FILTER_USE_BOTH);
    }

    protected function addArgs($match) {
        if (count($match) > 1) {
            array_shift($match);
            $this->setArgs(array_merge($this->regArgs, $match));
        }
    }

    protected function setArgs($args) {
        $this->regArgs = $args;
    }

    public function __toString() {
        $str = '';
        if (strlen($this->route) > 0) {
            $str = $this->name . ' method ' . $this->route . '\n';
        }
        if (count($this->children) > 0) {
            foreach ($this->children as $child) {
                $str .= $child->__toString();
            }
        }

        return $str;
    }

}
