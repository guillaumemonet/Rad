<?php

use Rad\classes\seller;

/*
 * Copyright (C) 2016 Guillaume Monet
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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