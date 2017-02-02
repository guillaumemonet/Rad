<?php

namespace scripts;

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

/**
 * Description of generate_test_controller
 *
 * @author Guillaume Monet
 */
require_once('../core/utils/Bootstrap.php');

use Rad\utils\Utils as Utils;

$dir = '../core/';
$files = array_diff(scandir($dir), array('..', '.'));
$test_dir = '../test/';
$p = array();
$u = array();
$ct = array();
foreach ($files as $cont) {

    $c = "<?php\n";
    $c .=" require_once('../core/utils/Bootstrap.php');";
    $c .=" Rad\manager\Response::doResponse('txt');";
    $cl = str_replace(".php", "", $cont);
    $cc = "\\Rad\\utils\\" . $cl;
    $u[$cl] = "use $cc as $cl;";
    $c.=$u[$cl] . "\n";
    $filename = $test_dir . "test_" . $cl . ".php";
    if (file_exists($filename)) {
	unlink($filename);
    }

    $file = fopen($filename, "w+");

    $reflection = new \ReflectionClass($cc);
    $methods = $reflection->getMethods(\ReflectionMethod::IS_STATIC);
    foreach ($methods as $method) {
	$params = $method->getParameters();
	$p = array();
	$pn = array();
	foreach ($params as $param) {
	    $p[] = "\$" . $param->getName();
	    $pn[] = "\\\$" . $param->getName();
	}
	$c.="echo \"Testing :" . $method->getName() . "(" . implode(",", $pn) . ")\\n\";\n";
	$m = Utils::parseComments($method->getDocComment());
	$test = 0;
	for ($i = 1; $i < 10; $i++) {
	    if (isset($m["pre$i"]) && isset($m["post$i"])) {
		$c.="echo \"Processing Test $i :\";\n";
		$c.=strip_tags($m["pre$i"]) . "\n";
		$c.= "\$test$i = $cl::" . $method->getName() . "(" . implode(",", $p) . ");\n";
		$c.= strip_tags($m["post$i"]) . "\n\n";
		$c.= "if(\$result === \$test$i){\n";
		$c.="\t echo \"SUCCESS\\n\";\n";
		$c.="} else {\n";
		$c.="\t echo \"FAILED [return '\$test$i'] [wanted '\$result']\\n\";\n";
		$c.="}\n\n";
		$test++;
	    }
	}
	if ($test == 0) {
	    $c.="echo \"No Test Found !\\n\\n\";\n";
	} else {
	    $c.="echo \"End Test : count $test\\n\\n\";\n";
	}
    }
    fwrite($file, $c);
}