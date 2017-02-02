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
namespace scripts;


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