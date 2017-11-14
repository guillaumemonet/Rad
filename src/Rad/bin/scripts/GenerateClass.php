<?php

namespace Rad\bin\scripts;

use Rad\bin\scripts\templates\DAOTemplate;
use Rad\Log\Log;
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

            Log::getHandler()->debug('Generating ' . $class . ' to ' . $this->pathClass);
            $daomodel = new DAOTemplate($class, $name, $table);
            $daomodel->setClassNamespace($this->namespaceClass)
                    ->setControllerNamespace($this->namespaceController)
                    ->setImpNamespace($this->namespaceImp);
            $this->printClass($daomodel);
            $this->printTrait($daomodel);
            $this->printController($daomodel);
        }
    }

    public function printClass(DAOTemplate $daomodel) {
        $filenameClass = $this->pathClass . '/' . $daomodel->className . '.php';
        file_exists($filenameClass) ? unlink($filenameClass) : '';
        $fileClass = fopen($filenameClass, "w+");
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
        fwrite($fileClass, $c);
    }

    public function printTrait(DAOTemplate $daomodel) {
        $filenameTrait = $this->pathClass . '/' . $daomodel->className . 'Trait.php';
        file_exists($filenameTrait) ? unlink($filenameTrait) : '';
        $fileTrait = fopen($filenameTrait, "w+");
        $c = StringUtils::println("<?php");
        $c .= $daomodel->printNamespace();
        $c .= $daomodel->printUseClasses();
        $c .= $daomodel->printStartTrait();
        $c .= $daomodel->printIndexesGetter();
        $c .= $daomodel->printEndClass();
        fwrite($fileTrait, $c);
    }

    public function printController(DAOTemplate $daomodel) {
        $filenameController = $this->pathController . '/' . $daomodel->className . 'BaseController.php';
        file_exists($filenameController) ? unlink($filenameController) : '';
        $fileController = fopen($filenameController, "w+");
        $c = StringUtils::println("<?php");
        $c .= $daomodel->printControllerNamespace();
        $c .= $daomodel->printControllerUseClasses();
        $c .= $daomodel->printStartController();
        $c .= $daomodel->printControllerGetAll();
        $c .= $daomodel->printControllerGetOne();
        $c .= $daomodel->printControllerPostOne();
        $c .= $daomodel->printControllerDeleteOne();
        $c .= $daomodel->printControllerPutOne();
        $c .= $daomodel->printControllerPatchOne();
        $c .= $daomodel->printEndClass();
        fwrite($fileController, $c);
    }

}
