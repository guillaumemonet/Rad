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

namespace Rad\Cache;

use Psr\SimpleCache\CacheInterface;
use Rad\Database\Database;

/**
 * MySQL CacheHandler
 *
 * Table definition:
 * <pre>CREATE TABLE IF NOT EXISTS `output_cache` (
 *   `id` CHAR(40) NOT NULL COMMENT 'sha1 hash',
 *   `modified` INT,
 *   `content` LONGTEXT NOT NULL,
 *   PRIMARY KEY (`id`),
 *   INDEX(`modified`)
 * ) ENGINE = InnoDB;</pre>
 */
final class Mysql_CacheHandler implements CacheInterface {

    private $read = "SELECT content FROM output_cache WHERE id IN(%s)";
    private $write = "INSERT INTO output_cache (id,modified,content) VALUES(\"%s\",%d,\"%s\") ON DUPLICATE KEY UPDATE content=\"%s\",modified=%d";
    private $purge = "DELETE FROM output_cache WHERE modified < %d";
    private $delete = "DELETE FROM output_cache WHERE id=\"%s\"";

    public function read(array $keys) {
        $ret = array();
        foreach ($keys as $k) {
            $_k = sha1($k);
            $r = sprintf($this->read, $_k);
            $res = Database::query($r);
            if ($row = Database::fetch_assoc($res)) {
                $ret[$k] = stripslashes($row["content"]);
            }
        }
        return $ret;
    }

    public function write(array $keys, $expire = null) {
        foreach ($keys as $k => $v) {
            $_k = sha1($k);
            $r = sprintf($this->write, $_k, time(), addslashes($v), addslashes($v), time());
            Database::query($r);
        }
    }

    public function delete(array $keys) {
        foreach ($keys as $k) {
            $_k = sha1($k);
            Database::query(sprintf($this->delete, $_k));
        }
    }

    public function purge() {
        Database::query(sprintf($this->purge, time()));
    }

    public function clear(): bool {
        
    }

    public function deleteMultiple($keys): bool {
        
    }

    public function get($key, $default = null) {
        
    }

    public function getMultiple($keys, $default = null): \Psr\SimpleCache\iterable {
        
    }

    public function has($key): bool {
        
    }

    public function set($key, $value, $ttl = null): bool {
        
    }

    public function setMultiple($values, $ttl = null): bool {
        
    }

}

?>
