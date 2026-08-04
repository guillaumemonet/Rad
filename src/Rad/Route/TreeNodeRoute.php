<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
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
    protected string $path_chunk;

    /**
     *
     * @var Route
     */
    protected ?Route $route = null;

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
            // Several sibling patterns may match the same segment (e.g. a
            // "(?<slug>[a-z0-9\-]+)" node and a "(?<id>[0-9]+)" node both
            // matching "2"). Try each in turn and keep the first that resolves
            // to a route further down, so an ambiguous prefix does not shadow a
            // deeper, valid match (backtracking).
            foreach ($matching_nodes as $node) {
                $route = $node->getRoute($array);
                if ($route !== null) {
                    return $route;
                }
            }
            return null;
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
            $str = $this->path_chunk . ' method ' . $this->route . '\n';
        }
        if (count($this->children) > 0) {
            foreach ($this->children as $child) {
                $str .= $child;
            }
        }

        return $str;
    }
}
