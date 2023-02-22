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

namespace Rad\Http;

/**
 * Description of Buffer
 *
 * @author guillaume
 */
abstract class Buffer {

    private function __construct() {
        
    }

    private function __clone() {
        
    }

    /**
     * 
     */
    public static function start() {
        ignore_user_abort(true); //avoid apache to kill the php running
        ob_start(); //start buffer output
    }

    /**
     * 
     */
    public static function end() {
        header("Content-Encoding: none"); //send header to avoid the browser side to take content as gzip format
        header("Content-Length: " . ob_get_length()); //send length header
        header("Connection: close"); //or redirect to some url: header('Location: http://www.google.com');
        ob_end_flush();
        flush(); //really send content, can't change the order:1.ob buffer to normal buffer, 2.normal buffer to output
        fastcgi_finish_request();
    }

}
