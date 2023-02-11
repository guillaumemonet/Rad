<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
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
    public function prepare($sql, $options = null) {
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

    /**
     * 
     * @param PDOStatement $rid
     * @return type
     */
    public function fetch_assoc(PDOStatement $rid) {
        return $rid->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * 
     * @param PDOStatement $rid
     * @return type
     */
    public function fetch_object(PDOStatement $rid) {
        return $rid->fetch(\PDO::FETCH_OBJ);
    }

    /**
     * 
     * @param PDOStatement $rid
     * @param type $fetch_style
     * @return type
     */
    public function fetch(PDOStatement $rid, $fetch_style) {
        return $rid->fetch($fetch_style);
    }

}
