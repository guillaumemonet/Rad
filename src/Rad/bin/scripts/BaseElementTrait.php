<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Rad\bin\scripts;

/**
 * Description of BaseElementTrait
 *
 * @author guillaume
 */
trait BaseElementTrait {

    public function getPrimaryColumns($type) {
        $ret = array();
        foreach ($this->columns as $col_name => $col) {
            if ($col->key == "PRI") {
                $ret[] = $col->getAsVar($type);
            }
        }
        return $ret;
    }

    public function getAutoIncPrimaryColumns($type) {
        $ret = array();
        foreach ($this->columns as $col_name => $col) {
            if ($col->key == "PRI" && $col->auto > 0) {
                $ret[] = $col->getAsVar($type);
            }
        }
        return $ret;
    }

    public function getNotPrimaryColumns($type, $ignoreSpecial = false) {
        $ret = array();
        foreach ($this->columns as $col_name => $col) {
            if ($col->key !== "PRI" && (!$ignoreSpecial || !in_array($col_name, array("password", "token")))) {
                $ret[] = $col->getAsVar($type);
            }
        }
        return $ret;
    }

    public function getNotAutoIncColumns($type) {
        $ret = array();
        foreach ($this->columns as $col_name => $col) {
            if ($col->auto == 0) {
                $ret[] = $col->getAsVar($type);
            }
        }
        return $ret;
    }

    public function getColumns($type, $ignoreSpecial = false) {
        $ret = array();
        foreach ($this->columns as $col_name => $col) {
            if (!$ignoreSpecial || (!in_array($col_name, array("password", "token")))) {
                $ret[] = $col->getAsVar($type);
            }
        }
        return $ret;
    }

}
