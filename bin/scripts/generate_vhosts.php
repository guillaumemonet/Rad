<?php
/*
 * The MIT License
 *
 * Copyright 2017 Guillaume Monet.
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
use Rad\classes\seller;


require("../core/utils/Bootstrap.php");


$vhosts = array(
    'operator' => file_get_contents("conf/operator_skel.conf"),
    'website' => file_get_contents("conf/website_skel.conf"),
    'cdn' => file_get_contents("conf/cdn_skel.conf"),
    'api' => file_get_contents("conf/api_skel.conf")
);
$sellers = seller::getAllSeller();
foreach ($sellers as $seller) {
    $domain = $seller->domain;
    $short_name = $seller->slug;
    $ip = $seller->ip;
    foreach ($vhosts as $vhost_name => $vhost_content) {
	$ret = str_replace("{SHORTNAME}", $short_name, str_replace("{IP}", $ip, str_replace("{DOMAIN}", $domain, $vhost_content)));
	file_put_contents("../vhosts/vhost_" . $short_name . "_" . $vhost_name . ".conf", $ret);
    }
}