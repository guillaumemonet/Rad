<?php

namespace Rad\manager\sessions;

use SessionHandlerInterface;

/**
 * Description of Database_SessionHandler
 *
 * @author Guillaume Monet
 */
final class Database_Handler implements SessionHandlerInterface {

    private $time;

    function __construct() {
	ini_set('session.serialize_handler', 'php_serialize');
	$this->time = time();
    }

    /**
     * 
     * @param type $path
     * @param type $name
     * @return boolean
     */
    public function open($path, $name) {
	//remove old sessions
	//$this->gc((int) bb::$conf->getConfig()->session->lifetime);
	return true;
    }

    /**
     * 
     * @return boolean
     */
    public function close() {
	return true;
    }

    /**
     * 
     * @param type $sessionId
     * @return type
     */
    public function read($sessionId) {
	$sessionId = bb::$database->real_escape_string($sessionId);
	bb::$database->session("START TRANSACTION", false, true);
	$sql = sprintf("SELECT datas FROM sessions.sessions_boutique WHERE sid = '%s' AND sid != '' FOR UPDATE", $sessionId);
	$result = bb::$database->session($sql);
	$data = '';
	if ($result) {
	    $record = bb::$database->fetch_array($result);
	    $data = $record['datas'];
	}
	return $data;
    }

    /**
     * 
     * @param type $sessionId
     * @param type $data
     */
    public function write($sessionId, $data) {
	$sessionId = bb::$database->real_escape_string($sessionId);
	$data = bb::$database->real_escape_string($data);

	if (!bb::$request->isBot()) {
	    if ($data == "a:0:{}" && false) {
		bb::$logs->error($_SERVER['HTTP_HOST'] . '' . $_SERVER['REQUEST_URI']);
		bb::$logs->error($_SERVER['REMOTE_ADDR']);
		bb::$logs->error($_SERVER['HTTP_USER_AGENT']);
	    }
	    $sql = sprintf("INSERT INTO sessions.sessions_boutique (sid,datas,expire) VALUES('%s', \"%s\", %d) ON DUPLICATE KEY UPDATE datas=\"%s\",expire=%d", $sessionId, $data, $this->time, $data, $this->time);
	    bb::$database->session($sql);
	    if ($sessionId == "") {
		bb::$logs->error("SESSION_ID VIDE" . $_SERVER['HTTP_USER_AGENT']);
	    }
	    //$sql = sprintf("INSERT INTO sessions.sessions_boutique2 (sid,datas,expire) VALUES('%s', \"%s\", %d) ON DUPLICATE KEY UPDATE datas=\"%s\",expire=%d", $sessionId, $data,$this->time, $data,$this->time);
	    //bb::$database->session($sql);
	} else {
	    //bb::$logs->error("NO SESSION USER_AGENT:" . $_SERVER['HTTP_USER_AGENT']);
	}
	bb::$database->session("COMMIT");
    }

    /**
     * 
     * @param type $sessionId
     */
    public function destroy($sessionId) {
	$sessionId = bb::$database->real_escape_string($sessionId);
	$sql = sprintf("DELETE FROM sessions.sessions_boutique WHERE sid = '%s' ", $sessionId);
	bb::$database->session($sql);
    }

    /**
     * 
     * @param type $age
     */
    public function gc($age) {
	$sql = sprintf("DELETE FROM sessions.sessions_boutique WHERE expire < %d", ($this->time - $age));
	bb::$database->session($sql);
    }

}

?>
