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

namespace Rad\bin\scripts;

use PDO;
use Rad\bin\scripts\elements\Column;
use Rad\bin\scripts\elements\Index;
use Rad\bin\scripts\elements\Table;
use Rad\Database\Database;

/**
 * Description of GenerateTrait
 *
 * @author guillaume
 */
trait GenerateTrait {

    private function generateArrayTables() {
        Database::getHandler()->change($this->database);
        $sql = "SHOW TABLES FROM " . $this->database;
        error_log($sql);
        $res_tables = Database::getHandler()->query($sql);

        $tables = array();
        while ($row = $res_tables->fetch()) {
            $table = new Table();
            $table->name = $row[0];
            $table->columns = $this->getTableStructure($table->name);
            $table->indexes = $this->getTableIndexes($table->name, $table->columns);
            $table->onetomany = $this->getOneToManyTable($table->name);
            $table->manytomany = $this->getManyToManyTable($table->name);
            $tables[$table->name] = $table;
        }
        return $tables;
    }

    public function getTableIndexes($table, $columns) {
        $indexes = array();
        $sql = "SHOW KEYS FROM `$table` WHERE 1";
        $rows = Database::getHandler()->query($sql);
        while ($tkey = $rows->fetch()) {
            if (!isset($indexes[$tkey["Key_name"]])) {
                $index = new Index();
                $index->name = $tkey["Key_name"];
                $index->unique = !$tkey["Non_unique"];
                $index->columns[$tkey["Column_name"]] = $columns[$tkey["Column_name"]];
                $indexes[$tkey["Key_name"]] = $index;
            } else {
                $indexes[$tkey["Key_name"]]->columns[$tkey["Column_name"]] = $columns[$tkey["Column_name"]];
            }
        }
        return $indexes;
    }

    public function getTableStructure($table) {
        $columns = array();
        $result = Database::getHandler()->query("SHOW FULL COLUMNS FROM `$table`;");
        while ($row = $result->fetch()) {
            $column = new Column();
            $column->name = $row["Field"];
            $this->setType($row, $column);
            $column->key = $row["Key"];
            $column->default = isset($row["Default"]) ? $row["Default"] : null;
            if (isset($row["Extra"]) && $row["Extra"] == "auto_increment") {
                $column->auto = 1;
            }
            $columns[$column->name] = $column;
        }
        return $columns;
    }

    private function setType($row, $column) {
        if (strstr($row["Type"], "char") !== false || strstr($row["Type"], "text") !== false) {
            $column->type_sql = "\\PDO::PARAM_STR";
            $column->type_php = "string";
        } else if (strstr($row["Type"], "tinyint") !== false) {
            $column->type_sql = "\\PDO::PARAM_INT";
            $column->type_php = "boolean";
        } else if (strstr($row["Type"], "blob") !== false) {
            $column->type_sql = "\\PDO::PARAM_LOB";
            $column->type_php = "binary";
        } else if (strstr($row["Type"], "int") !== false) {
            $column->type_sql = "\\PDO::PARAM_INT";
            $column->type_php = "int";
        } else if (strstr("float", $row["Type"]) !== false || strstr("long", $row["Type"]) !== false || strstr("double", $row["Type"]) !== false) {
            $column->type_sql = "\\PDO::PARAM_STR";
            $column->type_php = "decimal";
        } else {
            $column->type_sql = "\\PDO::PARAM_STR";
            $column->type_php = "string";
        }
    }

    public function getManyToManyTable($table) {
        $tables = array();
        $sql = "SHOW TABLES FROM bb WHERE Tables_in_bb LIKE '" . $table . "_has_%'";
        $link_tables = Database::getHandler()->query($sql);
        while ($link_table = $link_tables->fetch(PDO::FETCH_NUM)) {
            $ext = str_replace($table . "_has_", "", $link_table[0]);
            $tables[] = array("from" => $table, "by" => $link_table[0], "to" => $ext);
        }
        return $tables;
    }

    public function getOneToOneTable($table) {
        
    }

    public function getOneToManyTable($table) {
        $tables = array();
        $fk_tables = "SELECT TABLE_NAME,GROUP_CONCAT(CONCAT(COLUMN_NAME,'#',REFERENCED_COLUMN_NAME) separator ',') AS cols
FROM
  information_schema.KEY_COLUMN_USAGE
WHERE
  REFERENCED_TABLE_NAME = '$table'
      AND TABLE_NAME NOT LIKE \"%_has_%\"
  AND TABLE_SCHEMA = 'bb' GROUP BY TABLE_NAME;";
        $link_tables = Database::getHandler()->query($fk_tables);
        while ($link_table = $link_tables->fetch()) {
            $tables[$link_table["TABLE_NAME"]] = array(
                "from" => $link_table["TABLE_NAME"],
                "ref" => explode(",", $link_table["cols"]),
                "to" => $table
            );
        }
        return $tables;
    }

}
