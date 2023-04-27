<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Build;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Rad\Build\DatabaseBuilder\ClassesGenerator;
use Rad\Build\DatabaseBuilder\ControllersGenerator;
use Rad\Build\DatabaseBuilder\GeneratorTools;
use Rad\Utils\StringUtils;

/**
 * 
 */
class DatabaseBuildHandler implements BuildInterface {

    private ?ClassesGenerator $classesGenerator     = null;
    private ?ControllersGenerator $controllersGenerator = null;

    public function __construct() {
        $this->classesGenerator     = new ClassesGenerator();
        $this->controllersGenerator = new ControllersGenerator();
    }

    public function build($namespace = "Rad\\Datas", $path = null) {
        $this->classesGenerator->namespace     = $namespace;
        $this->controllersGenerator->namespace = $namespace;
        if ($path === null) {
            $this->classesGenerator->path     = __DIR__ . '/../Datas/';
            $this->controllersGenerator->path = __DIR__ . '/../Datas/';
        } else {
            $this->classesGenerator->path     = $path;
            $this->controllersGenerator->path = $path;
        }
        $tables = GeneratorTools::generateArrayTables();
        $this->generateClasses($tables);

        return "Generated";
    }

    private function generateClasses(array $tables) {
        foreach ($tables as $name => $table) {
            if (strpos($name, "_has_") !== false) {
                continue;
            }
            $className = StringUtils::camelCase($name);

            $namespace = new PhpNamespace($this->classesGenerator->namespace);
            foreach ($this->classesGenerator->baseRequire as $n) {
                $namespace->addUse($n);
            }
            $class = $namespace->addClass($className);

            $this->classesGenerator->generateProperties($class, $table);
            $this->classesGenerator->generateTableFormat($class, $table);
            $this->classesGenerator->generateCreate($class, $table);
            $this->classesGenerator->generateRead($class, $table);
            $this->classesGenerator->generateUpdate($class, $table);
            $this->classesGenerator->generateDelete($class, $table);
            $this->classesGenerator->generateGetId($class, $table);
            $this->classesGenerator->generateParse($class, $table);
            $this->classesGenerator->generateOneToManys($class, $table);
            $this->classesGenerator->generateIndexesGetter($class, $table);
            $this->classesGenerator->generateGetAll($class, $table);
            $filename = $this->classesGenerator->path . $className . '.php';
            echo $filename;
            file_put_contents($filename, "<?php\n\n" . StringUtils::reindent((string) $namespace));
            $this->generateController($class);
        }
    }

    private function generateController(ClassType $mainClass) {
        $className = 'Controller' . StringUtils::camelCase($mainClass->getName());

        $namespace = new PhpNamespace($this->controllersGenerator->namespace);
        foreach ($this->controllersGenerator->baseRequire as $n) {
            $namespace->addUse($n);
        }
        $namespace->addUse($this->classesGenerator->namespace . '\\' . $mainClass->getName());

        $class = $namespace->addClass($className);
        $class->setExtends('Rad\\Controller\\Controller');

        $this->controllersGenerator->generateControllerGetAll($mainClass, $class);
        $this->controllersGenerator->generateControllerGetOne($mainClass, $class);
        $this->controllersGenerator->generateControllerPostOne($mainClass, $class);
        $this->controllersGenerator->generateControllerDeleteOne($mainClass, $class);
        $this->controllersGenerator->generateControllerPutOne($mainClass, $class);
        $this->controllersGenerator->generateControllerPatchOne($mainClass, $class);
        $filename = $this->controllersGenerator->path . $className . '.php';
        echo $filename;
        file_put_contents($filename, "<?php\n\n" . StringUtils::reindent((string) $namespace));
    }

}
