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
use Rad\Cache\Cache;
use Rad\Config\Config;
use Rad\Utils\StringUtils;

/**
 * 
 */
class DatabaseBuildHandler implements BuildInterface {

    private ?ClassesGenerator $classesGenerator     = null;
    private ?ControllersGenerator $controllersGenerator = null;

    public function __construct() {
        $config                     = Config::getServiceConfig('build', 'databasebuilder')->config;
        $this->classesGenerator     = new ClassesGenerator();
        $this->controllersGenerator = new ControllersGenerator();

        $this->classesGenerator->path          = Config::getApiConfig()->install_path . $config->classesPath;
        $this->classesGenerator->namespace     = $config->classesNamespace;
        $this->controllersGenerator->path      = Config::getApiConfig()->install_path . $config->controllersPath;
        $this->controllersGenerator->namespace = $config->controllersNamespace;
        $this->controllersGenerator->prefix    = $config->controllersPrefix;
    }

    public function build() {
        if (!is_dir($this->classesGenerator->path)) {
            mkdir($this->classesGenerator->path, 0777, true);
        }
        if (!is_dir($this->controllersGenerator->path)) {
            mkdir($this->controllersGenerator->path, 0777, true);
        }

        $tables = GeneratorTools::generateArrayTables();
        $this->generateClasses($tables);

        Cache::getHandler()->clear();
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
            $class->setExtends('Rad\\Model\\Model');

            $this->classesGenerator->generateProperties($class, $table);
            $this->classesGenerator->generateTableFormat($class, $table);
            $this->classesGenerator->generateConstruct($class, $table);
            $this->classesGenerator->generateCreate($class, $table);
            $this->classesGenerator->generateRead($class, $table);
            $this->classesGenerator->generateUpdate($class, $table);
            $this->classesGenerator->generateDelete($class, $table);
            $this->classesGenerator->generateGetId($class, $table);
            $this->classesGenerator->generateCacheName($class, $table);
            $this->classesGenerator->generateParse($class, $table);
            $this->classesGenerator->generateOneToManys($class, $table);
            $this->classesGenerator->generateIndexesGetter($class, $table);
            $this->classesGenerator->generateGetAll($class, $table);
            $filename = $this->classesGenerator->path . $className . '.php';
            echo $filename . "<br />";
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
        echo $filename . ' : ';
        if (!file_exists($filename)) {
            file_put_contents($filename, "<?php\n\n" . StringUtils::reindent((string) $namespace));
            echo " Generated";
        } else {
            echo " Exists";
        }
        echo '<br />';
    }

}
