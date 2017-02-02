<?php

namespace Rad\Cache;

/**
 * Volatile Cache 
 * To Share Var Between Function
 */
final class QuickCache {

    /**
     *
     * @var array
     */
    private $datas = array();

    /**
     * 
     * @param type $key
     * @param type $datas
     */
    public function setDatas($key, $datas) {
	$this->datas["key_" . $key] = $datas;
    }

    /**
     * 
     * @param type $key
     * @return type
     */
    public function getDatas($key) {
	if (isset($this->datas["key_" . $key])) {
	    return $this->datas["key_" . $key];
	} else {
	    return null;
	}
    }

}

?>