<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Build\DatabaseBuilder;

/**
 * Description of ControllerGenerator
 *
 * @author Guillaume Monet
 */
class ControllerGenerator extends BaseGenerator {

    private ?string $namespace = null;
    private ?string $path      = null;

    public function printController(DAOTemplate $daomodel) {
        $fileController = $this->createFile($this->pathController . '/' . $daomodel->className . 'BaseController.php');
        $c              = StringUtils::println("<?php");
        $c              .= $daomodel->printControllerNamespace();
        $c              .= $daomodel->printControllerUseClasses();
        $c              .= $daomodel->printStartController();
        $c              .= $daomodel->printControllerGetAll();
        $c              .= $daomodel->printControllerGetOne();
        $c              .= $daomodel->printControllerPostOne();
        $c              .= $daomodel->printControllerDeleteOne();
        $c              .= $daomodel->printControllerPutOne();
        $c              .= $daomodel->printControllerPatchOne();
        $c              .= $daomodel->printEndClass();
        fwrite($fileController, $c);
    }

}
