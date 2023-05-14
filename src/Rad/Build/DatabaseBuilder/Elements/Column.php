<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Build\DatabaseBuilder\Elements;

/**
 * Description of Column
 *
 * @author guillaume
 */
class Column {

    public $name;
    public $type_sql;
    public $type_php;
    public $auto = 0;
    public $param;
    public $default;
    public $key;

    /**
     * 
     * @param string $type
     * @return string
     */
    public function getAsVar(string $type) {
        return $this->generateFormatTab()[$type];
    }

    /**
     * 
     * @return array
     */
    private function generateFormatTab() {
        return [
            "name"       => $this->name,
            "sql_name"   => '`' . $this->name . '`',
            "php"        => '$' . $this->name,
            "php_prefix" => '' . $this->type_php . ' $' . $this->name,
            "sql_cond"   => $this->name == 'password' ? '`' . $this->name . '` = PASSWORD(:' . $this->name . ')' : '`' . $this->name . '` = :' . $this->name,
            "sql_param"  => $this->name == 'password' ? 'PASSWORD(:' . $this->name . ')' : ':' . $this->name,
            "bind"       => '":' . $this->name . '"' . ' => ' . ($this->type_php == 'bool' ? 'intval(' . $this->name . ')' : '$' . $this->name),
            "bind_this"  => '":' . $this->name . '"' . ' => ' . ($this->type_php == 'bool' ? 'intval($this->' . $this->name . ')' : '$this->' . $this->name),
            "this"       => '$this->' . $this->name
        ];
    }

}
