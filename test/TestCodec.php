<?php

require(__DIR__ . "/../vendor/autoload.php");

use Rad\Codec\Codec;

$codec = new Codec();
error_log($codec);
$initdatas = array("toto" => "zero");
$decode0 = $codec->serialize($initdatas, "json");
error_log(print_r($decode0, true));
$decode1 = $codec->deserialize($decode0, "json");
error_log(print_r($decode1, true));
$decode4 = $codec->serialize($decode1, "*");
error_log(print_r($decode4, true));
$decode5 = $codec->deserialize($decode4, "*");
error_log(print_r($decode5, true));
if ($initdatas == $decode5) {
    error_log("Success :)");
}


