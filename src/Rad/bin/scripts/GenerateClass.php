<?php

namespace Rad\bin\scripts;

use Rad\bin\scripts\templates\DAOTemplate;
use Rad\Utils\StringUtils;

/**
 * Description of GenerateClass
 *
 * @author guillaume
 */
trait GenerateClass {

    private function generateClass(array $tables) {

        foreach ($tables as $name => $table) {
            if (strpos($name, "_has_") !== false) {
                continue;
            }
            $class = StringUtils::camelCase($name);
            $filename = $this->pathClass . "/" . $class . ".php";
            file_exists($filename) ? unlink($filename) : '';
            $file = fopen($filename, "w+");
            $daomodel = new DAOTemplate($class, $this->namespaceClass, $name, $table);
            
            $c = StringUtils::println("<?php");
            $c .= $daomodel->printNamespace();
            $c .= $daomodel->printUseClasses();
            $c .= $daomodel->printStartClass();
            $c .= $daomodel->printAttributes();
            $c .= $daomodel->printContructor();
            $c .= $daomodel->printGetId();
            $c .= $daomodel->printToString();
            $c .= $daomodel->printParse();
            $c .= $daomodel->printCreate();
            $c .= $daomodel->printRead();
            $c .= $daomodel->printUpdate();
            $c .= $daomodel->printDelete();
            $c .= $daomodel->printOneToMany();
            $c .= $daomodel->printManyToMany();
            $c .= $daomodel->printEndClass();
            fwrite($file, $c);
        }
    }

}
