<?php

namespace scripts;

use Rad\utils\Utils;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

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

require_once('../core/utils/Bootstrap.php');
ini_set('xdebug.var_display_max_depth', 15);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);

$filename = "../core/api/api.php";


if (file_exists($filename)) {
    unlink($filename);
}

$file = fopen($filename, "w+");

$dir = '../core/controller/';
$files = array_diff(scandir($dir), array('..', '.'));

$p = array();
$u = array();
$ct = array();
foreach ($files as $cont) {
    $cl = str_replace(".php", "", $cont);
    $c = "\\Rad\\controller\\" . $cl;
    $u[$cl] = "use $c as $cl;";
    $reflection = new ReflectionClass($c);
    $methods = $reflection->getMethods(ReflectionMethod::IS_STATIC);
    foreach ($methods as $method) {
	$com = $method->getDocComment();
	$array_com = Utils::parseComments($com);
	if (isset($array_com["path"])) {
	    $m = $array_com["method"][0];
	    $path = explode("/", rtrim($m . $array_com["path"][0], '/'));
	    $temp = &$ct;
	    foreach ($path as $key) {
		$temp = &$temp[$key];
	    }
	    $params = $method->getParameters();
	    $temp[$method->getName()] = array();
	    foreach ($params as $param) {
//$param is an instance of ReflectionParameter
		$par = $param->getName();
		if ($param->getClass() !== null) {
		    $class = new ReflectionClass($param->getClass()->name);
		    $u[$class->getShortName()] = "use \\" . $class->getName() . " as " . $class->getShortName() . ";";
		    $p[$par] = $class->getName();
		} else {
		    $p[$par] = null;
		}
		$temp[$method->getName()][$param->getName()] = $p[$par];
		//Rajouter condition autogenerer du style admin=1 id_user=id_user
	    }
	    unset($temp);
	}
    }
}
var_dump($ct);

$c = "<?php

namespace Rad\\api;

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

require_once('../utils/Bootstrap.php');

use \\Rad\\manager\\Cookies as Cookies;
use \\Rad\\manager\\Config as Config;
use \\Rad\\manager\\Response as Response;
use \\Rad\\manager\\Request as Request;\n";
foreach ($u as $v) {
    $c.=$v . "\n";
}
$c .= "\n";
$c .= "\$token = Cookies::get(Config::get(\"cookie\", \"user\"));\n";
$c .= "\$token_customer = Cookies::get(Config::get(\"cookie\", \"customer\"));\n";
$c .= "\$format = isset(\$_GET[\"format\"])?\$_GET[\"format\"]:\"json\";\n";
$c .= "\$datas = json_decode(file_get_contents(\"php://input\"));\n";
$c .= "\$method = strtoupper(Request::getHeader('REQUEST_METHOD'));\n";
$c .= "\$path = explode('/',\$_GET['path']);\n\n";


//$c .= "Response::doResponse(\"json\");\n";
$c.="switch(\$method){\n\n";
foreach ($ct as $cont => $m) {
    $c.="\tcase '" . strtoupper($cont) . "':\n";
    $c.="\t\tswitch(strtolower(\$path[0]){)\n\n";
    foreach ($m as $k => $v) {
	$c.="\t\t\tcase '" . strtolower($k) . "':\n";
	$c.="\t\t\ttry {\n";
	$p = array();
	foreach ($v as $k1 => $v1) {
	    $p[] = "\$" . $k1;
	    if ($k1 != "token_admin" && $k1 != "token_manager" && $k1 != "token_shop") {
		if ($v1 == null) {
		    $c.= "\t\t\t\t\$$k1 = isset(\$_POST['$k1']) ? \$_POST['$k1'] : null;\n";
		} else {
		    /* $reflect = new ReflectionClass($v1);
		      $c.= "\t\t\t\t\$$k1 = new " . $reflect->getShortName() . "();\n";
		      $props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
		      foreach ($props as $prop) {
		      $cp = $prop->getName();
		      if ($cp != "slug" && $cp != "resource_uri") {
		      $c.= "\t\t\t\t\$" . $k1 . "->" . $cp . " = isset(\$_POST['$cp']) ? \$_POST['$cp'] : null;\n";
		      }
		      } */
		}
	    }
	}
	$c.= "\t\t\t\$response = new ResponseApi();\n";
	$c.= "\t\t\t\$response->setDatas($cont::$k(" . implode(",", $p) . "));\n";
	$c.= "\t\t\t\techo \$response->__toJSON();\n";
	$c.="\t\t\t} catch(\Exception \$ex){\n";
	$c.="\t\t\t\techo json_encode(\$ex->getMessage());\n";
	$c.="\t\t\t}\n";
	$c.="\t\t\tbreak;\n\n";
    }
    $c.="\t\t}\n";
    $c.="\t\tbreak;\n\n";
}
$c.="}\n";
fwrite($file, $c);

