<?php

namespace Rad\manager;

final class EasyRec {

    private $userid;
    private $sessionid;
    private $apikey;
    private $tenantid;
    private $request_url;

    private function init() {
	if (!isset($_SESSION["tracking_id"])) {
	    $_SESSION["tracking_id"] = uniqid("U");
	}
	if (!isset($_SESSION["tracking_session"])) {
	    $_SESSION["tracking_session"] = uniqid("S");
	}
	$this->userid = $_SESSION["tracking_id"];
	$this->sessionid = $_SESSION["tracking_session"];
	$this->apikey = Config::get("recommendation", "apikey");
	$this->tenantid = Config::get("recommendation", "tenant");
	$this->request_url = Config::get("recommendation", "url");
    }

    /**
     * When call for reco in email
     */
    public function changeTracking($uid) {
	$this->userid = $uid;
    }

    /**
     * When paiement occurs
     */
    public function clearSession() {
	unset($_SESSION["tracking_session"]);
    }

    private function request($get_params) {
	if ($get_params) {
	    $url = $this->request_url . $get_params . "&apikey=" . $this->apikey . "&tenantid=" . $this->tenantid;
	    $data = file_get_contents($url);
	    return $data;
	} else {
	    return 0;
	}
    }

    /**
     * 
     * @param type $itemid
     * @param type $itemdesc
     * @param type $itemurl
     * @return int
     */
    public function view($itemid, $itemdesc, $itemurl) {
	if (!empty($itemid) and ! empty($itemdesc) and ! empty($itemurl)) {
	    $itemdesc = html_entity_decode($itemdesc);
	    $itemdesc = str_replace(" ", "%20", $itemdesc);
	    $itemurl = str_replace("&", "%26", $itemurl);
	    return $this->request("/view?itemid=" . $itemid . "&itemdescription=" . $itemdesc . "&itemurl=" . $itemurl . "&userid=" . $this->userid . "&sessionid=" . $this->sessionid);
	} else {
	    return 0;
	}
    }

    /**
     * 
     * @param type $itemid
     * @param type $itemdesc
     * @param type $itemurl
     * @return int
     */
    public function buy($itemid, $itemdesc, $itemurl) {
	if (!empty($itemid) and ! empty($itemdesc) and ! empty($itemurl)) {
	    $itemdesc = html_entity_decode($itemdesc);
	    $itemdesc = str_replace(" ", "%20", $itemdesc);
	    $itemurl = str_replace("&", "%26", $itemurl);
	    return $this->request("/buy?itemid=" . $itemid . "&itemdescription=" . $itemdesc . "&itemurl=" . $itemurl . "&userid=" . $this->userid . "&sessionid=" . $this->sessionid);
	} else {
	    return 0;
	}
    }

    public function rate($itemid, $itemdesc, $itemurl, $rvalue) {
	if (!empty($itemid) and ! empty($itemdesc) and ! empty($itemurl) and ! empty($rvalue)) {
	    $itemdesc = html_entity_decode($itemdesc);
	    $itemdesc = str_replace(" ", "%20", $itemdesc);
	    $itemurl = str_replace("&", "%26", $itemurl);
	    return $this->request("/rate?itemid=" . $itemid . "&itemdescription=" . $itemdesc . "&itemurl=" . $itemurl . "&userid=" . $this->userid . "&sessionid=" . $this->sessionid . "&ratingvalue=" . $rvalue);
	} else {
	    return 0;
	}
    }

    /**
     * 
     * @param type $itemid
     * @param type $itemdesc
     * @param type $itemurl
     * @return int
     */
    public function cart($itemid, $itemdesc, $itemurl) {
	if (!empty($itemid) and ! empty($itemdesc) and ! empty($itemurl)) {
	    $itemdesc = html_entity_decode($itemdesc);
	    $itemdesc = str_replace(" ", "%20", $itemdesc);
	    $itemurl = str_replace("&", "%26", $itemurl);
	    return $this->request("/sendaction?actiontype=CART&itemid=" . $itemid . "&itemdescription=" . $itemdesc . "&itemurl=" . $itemurl . "&userid=" . $this->userid . "&sessionid=" . $this->sessionid);
	} else {
	    return 0;
	}
    }

    /**
     * 
     * @param type $itemid
     * @param type $n
     * @return int
     */
    public function alsoViewed($itemid, $n = 10) {
	if (!empty($itemid)) {
	    $rec = new SimpleXMLElement($this->request("/otherusersalsoviewed?itemid=" . $itemid . "&userid=" . $this->userid . "&numberOfResults=" . $n));
	    return $rec->recommendeditems->item;
	} else {
	    return 0;
	}
    }

    /**
     * 
     * @param type $itemid
     * @param type $n
     * @return int
     */
    public function alsoBought($itemid, $n = 10) {
	if (!empty($itemid)) {
	    $rec = new SimpleXMLElement($this->request("/otherusersalsobought?itemid=" . $itemid . "&userid=" . $this->userid . "&numberOfResults=" . $n));
	    return $rec->recommendeditems->item;
	} else {
	    return 0;
	}
    }

    /**
     * 
     * @param type $itemid
     * @param type $n
     * @return int
     */
    public function ratedGood($itemid, $n = 10) {
	if (!empty($itemid)) {
	    $rec = new SimpleXMLElement($this->request("/itemsratedgoodbyotherusers?itemid=" . $itemid . "&userid=" . $this->userid . "&numberOfResults=" . $n));
	    return $rec->recommendeditems->item;
	} else {
	    return 0;
	}
    }

    /**
     * 
     * @param type $user_id
     * @param type $n
     * @return type
     */
    public function recForUser($n = 10, $type = "view") {
	$rec = new SimpleXMLElement($this->request("/recommendationsforuser?actiontype=" . $type . "&userid=" . $this->userid . "&numberOfResults=" . $n . "&sessionid=" . $this->sessionid));
	return $rec->recommendeditems->item;
    }

    /**
     * 
     * @param type $itemid
     * @param type $n
     * @return type
     */
    public function relatedItems($itemid, $n = 10) {
	$rec = new SimpleXMLElement($this->request("/relateditems?itemid=" . $itemid . "&userid=" . $this->userid . "&numberOfResults=" . $n));
	return $rec->recommendeditems->item;
    }

    /**
     * 
     * @param type $type
     * @param type $n
     * @return type
     */
    public function actionHistory($type = "view", $n = 10) {
	$rec = new SimpleXMLElement($this->request("/actionhistoryforuser?actiontype=" . $type . "&userid=" . $this->userid . "&numberOfResults=" . $n));
	return $rec->recommendeditems->item;
    }

    /**
     * 
     * @param type $url
     * @return type
     */
    public function backtrackUrl($url) {
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_HEADER, TRUE);
	$curlData = curl_exec($curl);
	curl_close($curl);
	$a = explode("\r\n", $curlData);
	$ret = "";
	foreach ($a as $l) {
	    if (strpos($l, "Location") !== false) {
		$ret = trim(str_replace("Location:", "", $l));
	    }
	}
	return $ret;
    }

    /**
     * 
      return   id,type,description,url,[imageurl],[value]
      $timeRange => DAY,WEEK,MONTH,ALL
     */
    public function mostViewed($timeRange = "ALL", $n = 10) {
	$rec = new SimpleXMLElement($this->request("/mostvieweditems?timeRange=" . $timeRange . "&numberOfResults=" . $n));
	return $rec->recommendeditems->item;
    }

    /**
      return   id,type,description,url,[imageurl],[value]
      $timeRange => DAY,WEEK,MONTH,ALL
     */
    public function mostBought($timeRange = "ALL", $n = 10) {
	$rec = new SimpleXMLElement($this->request("/mostboughtitems?numberOfResults=" . $n . "&timeRange=" . $timeRange));
	return $rec->recommendeditems->item;
    }

    /**
      return   id,type,description,url,[imageurl],[value]
      $timeRange => DAY,WEEK,MONTH,ALL
     */
    public function mostRated($timeRange = "ALL", $n = 10) {
	$rec = new SimpleXMLElement($this->request("/mostrateditems?numberOfResults=" . $n . "&timeRange=" . $timeRange));
	return $rec->recommendeditems->item;
    }

    /**
      return   id,type,description,url,[imageurl],[value]
      $timeRange => DAY,WEEK,MONTH,ALL
     */
    public function bestRated($timeRange = "ALL", $n = 10) {
	$rec = new SimpleXMLElement($this->request("/bestrateditems?numberOfResults=" . $n . "&timeRange=" . $timeRange));
	return $rec->recommendeditems->item;
    }

    /**
      return   id,type,description,url,[imageurl],[value]
      $timeRange => DAY,WEEK,MONTH,ALL
     */
    public function worstRated($timeRange = "ALL", $n = 10) {
	$rec = new SimpleXMLElement($this->request("/worstrateditems?numberOfResults=" . $n . "&timeRange=" . $timeRange));
	return $rec->recommendeditems->item;
    }

    public function updateItem($id_article, $active) {
	$rec = new SimpleXMLElement($this->request("/setitemactive?itemid=" . $id_article . "&active=" . ($active ? "true" : "false")));
	return $rec->recommendeditems->item;
    }

}

?>
