<?php

/*
 * Copyright (C) 2016 Guillaume Monet
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

/**
 * Description of Database.
 * @author Guillaume Monet
 */

namespace Rad\Database;

use PDO;
use PDOException;
use PDOStatement;
use Rad\Config\Config;
use Rad\Log\Log;

final class Database {

    /**
     * @var PDO
     */
    private static $database = null;

    private function __construct() {
        
    }

    private static function connect() {
        try {
            self::$database = new PDO(Config::get('database', 'type') . ":host=" . Config::get('database', 'host') . ";dbname=" . Config::get('database', 'database'), Config::get('database', 'user'), Config::get('database', 'password'));
            self::$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            self::$database->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $ex) {
            Log::error($ex->getMessage());
        }
    }

    /**
     * @param string $dbname
     */
    public static function change($dbname) {
        if (!self::ping()) {
            self::connect();
        }
        try {
            self::$database->exec("USE " . $dbname);
        } catch (PDOException $ex) {
            Log::error($ex->getMessage());
        }
    }

    /**
     * @param string $sql
     *
     * @return PDOStatement
     */
    public static function prepare($sql) {
        if (!self::ping()) {
            self::connect();
        }
        try {
            Log::debug($sql);
            $stmt = self::$database->prepare($sql);
        } catch (PDOException $ex) {
            Log::error($ex->getMessage());
        }
        return $stmt;
    }

    /**
     * @param string $sql
     *
     * @return PDOStatement
     */
    public static function query($sql) {
        if (!self::ping()) {
            self::connect();
        }
        try {
            Log::debug($sql);
            return self::$database->query($sql);
        } catch (PDOException $ex) {
            Log::error($ex->getMessage());
        }
        return false;
    }

    /**
     * 
     * @param PDOStatement $stmt
     * @param type $mode
     * @return type
     */
    public static function fetch(PDOStatement $stmt, $mode = PDO::FETCH_ASSOC) {
        return $stmt->fetch($mode);
    }

    /**
     * 
     * @param PDOStatement $stmt
     * @return PDOStatement
     */
    public static function fetchAssoc(PDOStatement $stmt) {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 
     * @param PDOStatement $stmt
     * @return PDOStatement
     */
    public static function fetchArray(PDOStatement $stmt) {
        return $stmt->fetch(PDO::FETCH_NUM);
    }

    /**
     * 
     * @param PDOStatement $stmt
     * @param int $mode
     * @return PDOStatement
     */
    public static function fetchAll(PDOStatement $stmt, $mode = PDO::FETCH_ASSOC) {
        return $stmt->fetchAll($mode);
    }

    /**
     * 
     * @param PDOStatement $stmt
     * @param array $input_parameters
     * @return PDOStatement
     */
    public static function execute(PDOStatement $stmt, $input_parameters = null) {
        try {
            if ($input_parameters === null) {
                return $stmt->execute();
            } else {
                return $stmt->execute($input_parameters);
            }
        } catch (PDOException $ex) {
            Log::error($ex->getMessage());
        }
    }

    /**
     * 
     * @return boolean
     */
    public static function ping() {
        if (self::$database != null) {
            try {
                $status = self::$database->getAttribute(PDO::ATTR_CONNECTION_STATUS);
                if ($status === null) {
                    return false;
                } else {
                    return true;
                }
            } catch (PDOException $ex) {
                Log::error($ex->getMessage());
            }
        } else {
            return false;
        }
    }

    public static function schema($table) {
        return self::$database->query("SHOW COLUMUNS FROM `$table`");
    }

    public static function lastInsertId() {
        return self::$database->lastInsertId();
    }

}
