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

namespace Rad\Database;

use PDO;
use PDOException;
use PDOStatement;
use Rad\Config\Config;
use Rad\Log\Log;

/**
 * Description of Database.
 * @author Guillaume Monet
 */
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
