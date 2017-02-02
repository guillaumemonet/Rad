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
