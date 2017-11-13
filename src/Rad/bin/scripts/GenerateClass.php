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
            $filenameClass = $this->pathClass . '/' . $class . '.php';
            file_exists($filenameClass) ? unlink($filenameClass) : '';
            $fileClass = fopen($filenameClass, "w+");

            $filenameTrait = $this->pathClass . '/' . $class . 'Trait.php';
            file_exists($filenameTrait) ? unlink($filenameTrait) : '';
            $fileTrait = fopen($filenameTrait, "w+");

            $daomodel = new DAOTemplate($class, $this->namespaceClass, $name, $table);
            fwrite($fileClass, $this->printClass($daomodel));
            fwrite($fileTrait, $this->printTrait($daomodel));
        }
    }

    public function printClass(DAOTemplate $daomodel) {
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
        return $c;
    }

    public function printTrait(DAOTemplate $daomodel) {
        $c = StringUtils::println("<?php");
        $c .= $daomodel->printNamespace();
        $c .= $daomodel->printUseClasses();
        $c .= $daomodel->printStartTrait();
        $c .= $daomodel->printIndexesGetter();
        $c .= $daomodel->printEndClass();
        return $c;
    }

}
