<?php

require(__DIR__ . "/../vendor/autoload.php");

use Rad\Codec\Codec;
use Rad\Codec\CodecInterface;

class TestCodec implements CodecInterface {

    public function getMimeType() {
        return array("json");
    }

    public function __toString() {
        return "JSON Codec";
    }

    public function deserialize(string $string) {
        return json_decode($string);
    }

    public function serialize($object) {
        return json_encode($object);
    }

}

$testcodec = new TestCodec();
//Codec::add($testcodec);
error_log(Codec::add($testcodec)::listCodecs());
$datas = Codec::serialize("json", array("toto"));
error_log(json_last_error_msg());
error_log($datas);
$decode = Codec::deserialize("json", $datas);
error_log(print_r($decode, true));
$decode2 = Codec::deserialize("xml", $datas);



