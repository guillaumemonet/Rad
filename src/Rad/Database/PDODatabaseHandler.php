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

namespace Rad\Database;

use PDO;
use PDOException;
use PDOStatement;
use Rad\Config\Config;
use Rad\Log\Log;

/**
 * Description of DatabaseMysql
 *
 * @author guillaume
 */
class PDODatabaseHandler extends DatabaseAdapter {

    public function __construct() {
        $config = Config::getServiceConfig("database", "pdo")->config;
        try {
            parent::__construct($config->type . ":host=" . $config->host . ";dbname=" . $config->database, $config->user, $config->password);
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $this->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $ex) {
            Log::getHandler()->error($ex->getMessage());
        }
    }

    /**
     * @param string $dbname
     */
    public function change(string $dbname) {
        if (!$this->ping()) {
            $this->connect();
        }
        try {
            $this->exec("USE " . $dbname);
        } catch (PDOException $ex) {
            Log::getHandler()->error($ex->getMessage());
        }
    }

    /**
     * @param string $sql
     *
     * @return PDOStatement
     */
    public function prepare($sql, $options = []) {
        try {
            Log::getHandler()->debug($sql);
            $stmt = parent::prepare($sql, $options);
        } catch (PDOException $ex) {
            Log::getHandler()->error($ex->getMessage());
        }
        return $stmt;
    }

    /**
     * @param string $sql
     *
     * @return PDOStatement
     */
    public function query($sql) {
        try {
            Log::getHandler()->debug($sql);
            return parent::query($sql);
        } catch (PDOException $ex) {
            Log::getHandler()->error($ex->getMessage());
        }
        return false;
    }

    /**
     * 
     * @param PDOStatement $stmt
     * @param type $mode
     * @return mixed
     */
    public function fetch(PDOStatement $stmt, $mode = PDO::FETCH_ASSOC) {
        return $stmt->fetch($mode);
    }

    /**
     * 
     * @param PDOStatement $stmt
     * @return PDOStatement
     */
    public function fetchAssoc(PDOStatement $stmt) {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 
     * @param PDOStatement $stmt
     * @return PDOStatement
     */
    public function fetchArray(PDOStatement $stmt) {
        return $stmt->fetch(PDO::FETCH_NUM);
    }

    /**
     * 
     * @param PDOStatement $stmt
     * @param int $mode
     * @return PDOStatement
     */
    public function fetchAll(PDOStatement $stmt, $mode = PDO::FETCH_ASSOC) {
        return $stmt->fetchAll($mode);
    }

    /**
     * 
     * @param PDOStatement $stmt
     * @param array $input_parameters
     * @return PDOStatement
     */
    public function execute(PDOStatement $stmt, $input_parameters = null) {
        try {
            if ($input_parameters === null) {
                return $stmt->execute();
            } else {
                return $stmt->execute($input_parameters);
            }
        } catch (PDOException $ex) {
            Log::getHandler()->error($ex->getMessage());
        }
    }

    /**
     * 
     * @return boolean
     */
    public function ping() {
        try {
            $status = $this->getAttribute(PDO::ATTR_CONNECTION_STATUS);
            if ($status === null) {
                return false;
            } else {
                return true;
            }
        } catch (PDOException $ex) {
            Log::getHandler()->error($ex->getMessage());
        }
    }

    public function schema($table) {
        return $this->query("SHOW COLUMUNS FROM `$table`");
    }

}
