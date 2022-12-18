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

use PDO;
use Psr\SimpleCache\CacheInterface;
use Rad\Config\Config;
use Rad\Database\Database;
use Rad\Encryption\Encryption;

/**
 * Database CacheHandler
 *
 * Table definition example:
 * <pre>CREATE TABLE IF NOT EXISTS `output_cache` (
 *   `id` CHAR(40) NOT NULL COMMENT 'Encryption::hashMd5 hash',
 *   `modified` INT,
 *   `content` LONGTEXT NOT NULL,
 *   PRIMARY KEY (`id`),
 *   INDEX(`modified`)
 * ) ENGINE = InnoDB;</pre>
 */
class DatabaseCacheHandler implements CacheInterface {

    private $read   = "SELECT id,content FROM output_cache WHERE id IN(%s)";
    private $write  = "INSERT INTO output_cache (id,modified,content) VALUES (\"%s\",%d,\"%s\") ON DUPLICATE KEY UPDATE content=\"%s\",modified=%d";
    private $purge  = "DELETE FROM output_cache WHERE modified < %d";
    private $delete = "DELETE FROM output_cache WHERE id IN (\"%s\")";
    private $type   = null;

    public function __construct() {
        $this->type = isset(Config::getServiceConfig('cache', 'database')->config->type) ? Config::getServiceConfig('cache', 'database')->config->type : null;
    }

    public function delete($key) {
        Database::getHandler($this->type)->exec(sprintf($this->delete, Encryption::hashMd5($key)));
    }

    public function clear(): bool {
        return Database::getHandler($this->type)->exec(sprintf($this->purge, time())) !== false;
    }

    public function deleteMultiple($keys): bool {
        foreach ($keys as $k) {
            $this->delete($k);
        }
        return true;
    }

    public function get($key, $default = null) {
        $res = Database::getHandler($this->type)->query(sprintf($this->read, '"' . Encryption::hashMd5($key) . '"'));
        $row = $res->fetch(PDO::FETCH_ASSOC);
        if ($row !== false && $row !== null) {
            return $row["content"];
        } else {
            return $default;
        }
    }

    public function getMultiple($keys, $default = null) {
        $keys = array_flip($keys);
        array_walk($keys, function (&$value, $key) {
            $value = Encryption::hashMd5($key);
        });

        $res   = Database::getHandler($this->type)->query(sprintf($this->read, '"' . implode('","', $keys) . '"'));
        $datas = $res->fetchAll(PDO::FETCH_KEY_PAIR);
        array_walk($keys, function (&$value, $key) use ($datas) {
            $value = isset($datas[$value]) ? $datas[$value] : null;
        });
        return $keys;
    }

    public function has($key): bool {
        $res = Database::getHandler($this->type)->query(sprintf($this->read, '"' . Encryption::hashMd5($key) . '"'));
        return $row = $res->fetch();
    }

    public function set($key, $value, $ttl = null): bool {
        $time = time() + sprintf('%d', $ttl);
        $r    = sprintf($this->write, Encryption::hashMd5($key), $ttl, addslashes($value), addslashes($value), $time);
        return Database::getHandler($this->type)->exec($r) !== null;
    }

    public function setMultiple($values, $ttl = null): bool {
        $ret = false;
        foreach ($values as $key => $value) {
            $ret &= $this->set($key, $value, $ttl);
        }
        return $ret;
    }

}
