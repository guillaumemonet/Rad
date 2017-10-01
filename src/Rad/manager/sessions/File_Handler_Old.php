<?php

namespace Rad\manager\sessions;

use Rad\manager\Config as Config;
use SessionHandlerInterface;

/**
 * Description of File_Handler
 *
 * @author Guillaume Monet
 */
final class File_Handler_Old implements SessionHandlerInterface {

    private $savePath;

    public function __construct() {
	$this->savePath = Config::get("session_file", "path");
	ini_set("session.save_path", $this->savePath);
    }

    /**
     * 
     * @param type $save_path
     * @param type $name
     * @return boolean
     */
    function open($save_path, $name) {
	$this->savePath = $save_path;
	if (!is_dir($this->savePath)) {
	    mkdir($this->savePath, 0777);
	}
	return true;
    }

    /**
     * 
     * @return boolean
     */
    function close() {
	return true;
    }

    /**
     * 
     * @param string $id
     * @return string
     */
    function read($id) {
	return (string) file_get_contents($this->savePath . "/sess_$id");
    }

    /**
     * 
     * @param type $id
     * @param type $data
     * @return type
     */
    function write($id, $data) {
	return file_put_contents($this->savePath . "/sess_$id", $data) === false ? false : true;
    }

    /**
     * 
     * @param type $id
     * @return boolean
     */
    function destroy($id) {
	$file = $this->savePath . "/sess_$id";
	if (file_exists($file)) {
	    unlink($file);
	}
	return true;
    }

    /**
     * 
     * @param type $maxlifetime
     * @return boolean
     */
    function gc($maxlifetime) {
	foreach (glob($this->savePath . "/sess_*") as $file) {
	    if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
		unlink($file);
	    }
	}
	return true;
    }

}

?>
