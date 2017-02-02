<?php

/*
 * Copyright (C) 2017 Guillaume Monet
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Rad\Route;

use ErrorException;
use Rad\Api;
use Rad\Http\Error\NotFoundException;
use Rad\Log\Log;

/**
 * Description of ApiRoute
 *
 * @author Guillaume Monet
 */
final class Router {

    private $path_array = array();

    public function __construct() {
        
    }

    /**
     * 
     * @param string $path
     * @param string $function
     * @throws ErrorException
     */
    public function get($path, $function, $version = 1) {
        if (!isset($this->path_array[$version]["GET"][$path])) {
            $this->path_array[$version]["GET"][$path] = $function;
            Log::debug("GET Adding route " . $path);
        } else {
            throw new ErrorException("GET Path [$path] Already exist");
        }
    }

    /**
     * 
     * @param string $path
     * @param string $function
     * @throws ErrorException
     */
    public function post($path, $function, $version = 1) {
        if (!isset($this->path_array[$version]["POST"][$path])) {
            $this->path_array[$version]["POST"][$path] = $function;
            Log::debug("POST Adding route " . $path);
        } else {
            throw new ErrorException("POST Path [$path] Already exist");
        }
    }

    /**
     * 
     * @param string $path
     * @param string $function
     * @throws ErrorException
     */
    public function options($path, $function, $version = 1) {
        if (!isset($this->path_array[$version]["OPTIONS"][$path])) {
            $this->path_array[$version]["OPTIONS"][$path] = $function;
            Log::debug("OPTIONS Adding route " . $path);
        } else {
            throw new ErrorException("OPTIONS Path [$path] Already exist");
        }
    }

    /**
     * 
     * @param string $path
     * @param string $function
     * @throws ErrorException
     */
    public function put($path, $function, $version = 1) {
        if (!isset($this->path_array[$version]["PUT"][$path])) {
            $this->path_array[$version]["PUT"][$path] = $function;
            Log::debug("PUT Adding route " . $path);
        } else {
            throw new ErrorException("PUT Path [$path] Already exist");
        }
    }

    /**
     * 
     * @param string $path
     * @param string $function
     * @throws ErrorException
     */
    public function patch($path, $function, $version = 1) {
        if (!isset($this->path_array[$version]["PATCH"][$path])) {
            $this->path_array[$version]["PATCH"][$path] = $function;
            Log::debug("PATCH Adding route " . $path);
        } else {
            throw new ErrorException("PATCH Path [$path] Already exist");
        }
    }

    /**
     * 
     * @param string $path
     * @param string $function
     * @throws ErrorException
     */
    public function delete($path, $function, $version = 1) {
        if (!isset($this->path_array[$version]["DELETE"][$path])) {
            $this->path_array[$version]["DELETE"][$path] = $function;
            Log::debug("DELETE Adding route " . $path);
        } else {
            throw new ErrorException("DELETE Path [$path] Already exist");
        }
    }

    public function __toString() {
        return print_r($this->path_array, true);
    }

    /**
     * 
     * @param type $version
     * @param type $method
     * @param type $path
     * @param type $request
     * @param type $response
     * @param type $middle
     * @return type
     * @throws NotFoundException
     */
    public function route(Api &$api) {
        $version = $api->getRequest()->version;
        $method = $api->getRequest()->method;
        $path = $api->getRequest()->path;

        if (isset($this->path_array[$version][$method])) {
            $found = false;
            foreach ($this->path_array[$version][$method] as $reg_path => $function) {
                $p = "/^" . str_replace("/", "\/", (trim($reg_path, "/"))) . "$/";
                if (preg_match($p, $path, $m)) {
                    $found = true;
                    unset($m[0]);
                    $api->getRequest()->path_datas = $m;
                    Log::debug($method . " : " . $path . " Matching " . $p);
                    return call_user_func_array($function, array(&$api));
                }
            }
            if (!$found) {
                throw new NotFoundException("No route found for " . $path);
            }
        } else {
            throw new NotFoundException("No Method " . $method . " found for " . $path);
        }
    }

}
