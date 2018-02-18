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
            $this->exec('USE ' . $dbname);
        } catch (PDOException $ex) {
            Log::getHandler()->error($ex->getMessage());
        }
    }

    /**
     * @param string $sql
     *
     * @return PDOStatement
     */
    public function prepare(string $sql, $options = []) {
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

    public function exec($sql) {
        try {
            Log::getHandler()->debug($sql);
            return parent::exec($sql);
        } catch (PDOException $ex) {
            Log::getHandler()->error($ex->getMessage());
        }
        return false;
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

    /**
     * 
     * @param string $table_name
     * @return type
     */
    public function describeTable(string $table_name) {
        $statment = $this->prepare(sprintf('DESCRIBE %s', $table_name));
        $statment->execute();
        return $statment->fetchAll(\PDO::FETCH_ASSOC);
    }

}
