<?php

namespace Rad\Cache;

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
final class Mysql_Handler extends ICacheManager {

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

}

?>
