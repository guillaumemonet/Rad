<?php

declare(strict_types=1);

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Cache;

use DateInterval;
use PDO;
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
class DatabaseCacheHandler extends AbstractCacheHandler {
    private string $read   = 'SELECT id,content FROM output_cache WHERE id IN(%s)';
    private string $write  = 'INSERT INTO output_cache (id,modified,content) VALUES ("%s",%d,"%s") ON DUPLICATE KEY UPDATE content="%s",modified=%d';
    private string $purge  = 'DELETE FROM output_cache WHERE modified < %d';
    private string $clear  = 'TRUNCATE output_cache';
    private string $delete = 'DELETE FROM output_cache WHERE id IN ("%s")';
    private ?string $type  = null;

    public function __construct() {
        $this->type = Config::getServiceConfig('cache', 'database')->config->type ?? null;
    }

    public function delete(string $key): bool {
        return Database::getHandler($this->type)->exec(sprintf($this->delete, Encryption::hashMd5($key))) !== false;
    }

    public function purge(): bool {
        return Database::getHandler($this->type)->exec(sprintf($this->purge, time())) !== false;
    }

    public function clear(): bool {
        return Database::getHandler($this->type)->exec($this->clear) !== false;
    }

    public function deleteMultiple(iterable $keys): bool {
        $ret = true;
        foreach ($keys as $k) {
            $ret = $this->delete($k) && $ret;
        }
        return $ret;
    }

    public function get(string $key, mixed $default = null): mixed {
        $res = Database::getHandler($this->type)->query(sprintf($this->read, '"' . Encryption::hashMd5($key) . '"'));
        $row = $res->fetch(PDO::FETCH_ASSOC);
        return ($row !== false && $row !== null) ? $row['content'] : $default;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable {
        $map = [];
        foreach ($keys as $k) {
            $map[Encryption::hashMd5($k)] = $k;
        }
        if ($map === []) {
            return [];
        }
        $res  = Database::getHandler($this->type)->query(sprintf($this->read, '"' . implode('","', array_keys($map)) . '"'));
        $rows = $res->fetchAll(PDO::FETCH_KEY_PAIR);
        $ret  = [];
        foreach ($map as $hash => $original) {
            $ret[$original] = $rows[$hash] ?? $default;
        }
        return $ret;
    }

    public function has(string $key): bool {
        $res = Database::getHandler($this->type)->query(sprintf($this->read, '"' . Encryption::hashMd5($key) . '"'));
        return $res->fetch() !== false;
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool {
        $time    = time() + ($this->ttlToSeconds($ttl) ?? 0);
        $content = addslashes((string) $value);
        $sql     = sprintf($this->write, Encryption::hashMd5($key), $time, $content, $content, $time);
        return Database::getHandler($this->type)->exec($sql) !== false;
    }

    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool {
        $ret = true;
        foreach ($values as $key => $value) {
            $ret = $this->set($key, $value, $ttl) && $ret;
        }
        return $ret;
    }
}
