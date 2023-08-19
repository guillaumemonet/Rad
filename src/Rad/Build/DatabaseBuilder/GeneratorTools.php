<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Build\DatabaseBuilder;

use PDO;
use Rad\Build\DatabaseBuilder\Elements\Column;
use Rad\Build\DatabaseBuilder\Elements\Index;
use Rad\Build\DatabaseBuilder\Elements\Table;
use Rad\Database\Database;

/**
 * Description of Tools
 *
 * @author Guillaume Monet
 */
class GeneratorTools {

    public static function setType($row, $column) {

        $type  = preg_replace('/\s*\([^)]*\)/', '', strtolower($row["Type"]));
        $types = [
            "char"    => ["\\PDO::PARAM_STR", "string"],
            "varchar" => ["\\PDO::PARAM_STR", "string"],
            "text"    => ["\\PDO::PARAM_STR", "string"],
            "tinyint" => ["\\PDO::PARAM_INT", "bool"],
            "blob"    => ["\\PDO::PARAM_BLOB", "binary"],
            "int"     => ["\\PDO::PARAM_INT", "int"],
            "float"   => ["\\PDO::PARAM_STR", "float"],
            "long"    => ["\\PDO::PARAM_STR", "float"],
            "double"  => ["\\PDO::PARAM_STR", "float"],
            "decimal" => ["\\PDO::PARAM_STR", "float"]
        ];

        if (array_key_exists($type, $types)) {
            $column->type_sql = $types[$type][0];
            $column->type_php = $types[$type][1];
        } else {
            $column->type_sql = "\\PDO::PARAM_STR";
            $column->type_php = "string";
        }
    }

    public static function generateArrayTables() {
        $sql        = "SHOW TABLES";
        $res_tables = Database::getHandler()->query($sql);

        $tables = [];
        while ($row    = $res_tables->fetch()) {
            $table                = new Table();
            $table->name          = $row[0];
            $table->columns       = self::getTableStructure($table->name);
            $table->indexes       = self::getTableIndexes($table->name, $table->columns);
            $table->onetomany     = self::getOneToManyTable($table->name);
            $table->manytomany    = self::getManyToManyTable($table->name);
            $tables[$table->name] = $table;
        }
        return $tables;
    }

    public static function getTableIndexes($table, $columns) {
        $indexes = [];
        $sql     = "SHOW KEYS FROM `$table` WHERE 1";
        $rows    = Database::getHandler()->query($sql);
        while ($tkey    = $rows->fetch()) {
            if (!isset($indexes[$tkey["Key_name"]])) {
                $index                                = new Index();
                $index->name                          = $tkey["Key_name"];
                $index->unique                        = !$tkey["Non_unique"];
                $index->columns[$tkey["Column_name"]] = $columns[$tkey["Column_name"]];
                $indexes[$tkey["Key_name"]]           = $index;
            } else {
                $indexes[$tkey["Key_name"]]->columns[$tkey["Column_name"]] = $columns[$tkey["Column_name"]];
            }
        }
        return $indexes;
    }

    public static function getTableStructure($table) {
        $columns = [];
        $result  = Database::getHandler()->query("SHOW FULL COLUMNS FROM `$table`;");
        while ($row     = $result->fetch()) {
            $column       = new Column();
            $column->name = $row["Field"];
            self::setType($row, $column);
            $column->key  = $row["Key"];
            if (isset($row["Default"])) {
                settype($row["Default"], $column->type_php);
                $column->default = $row["Default"];
            }
            if (isset($row["Extra"]) && $row["Extra"] == "auto_increment") {
                $column->auto = 1;
            }
            if (!in_array($column->name, ['date_rec', 'date_update'])) {
                $columns[$column->name] = $column;
            }
        }
        return $columns;
    }

    public static function getOneToManyTable($table) {
        $tables      = [];
        $fk_tables   = "SELECT CONSTRAINT_NAME ,TABLE_NAME,GROUP_CONCAT(CONCAT(COLUMN_NAME,'#',REFERENCED_COLUMN_NAME) separator ',') AS cols
FROM
  information_schema.KEY_COLUMN_USAGE
WHERE
  REFERENCED_TABLE_NAME = '$table'
      AND TABLE_NAME NOT LIKE \"%_has_%\"
  GROUP BY CONSTRAINT_NAME;";
        $link_tables = Database::getHandler()->query($fk_tables);
        while ($link_table  = $link_tables->fetch()) {
            $tables[$link_table["CONSTRAINT_NAME"]] = array(
                "from" => $link_table["TABLE_NAME"],
                "ref"  => explode(",", $link_table["cols"]),
                "to"   => $table
            );
        }
        return $tables;
    }

    public static function getManyToManyTable($table) {
        $tables      = [];
        $sql         = "SHOW TABLES LIKE '" . $table . "_has_%'";
        $link_tables = Database::getHandler()->query($sql);
        while ($link_table  = $link_tables->fetch(PDO::FETCH_NUM)) {
            $ext      = str_replace($table . "_has_", "", $link_table[0]);
            $tables[] = array("from" => $table, "by" => $link_table[0], "to" => $ext);
        }
        return $tables;
    }

}
