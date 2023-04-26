<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Build;

use Nette\PhpGenerator\PhpNamespace;
use Rad\Build\DatabaseBuilder\ClassesGenerator;
use Rad\Build\DatabaseBuilder\GeneratorTools;
use Rad\Utils\StringUtils;

/**
 * 
 */
class DatabaseBuildHandler implements BuildInterface {

    private $classesGenerator = null;

    public function __construct() {
        $this->classesGenerator = new ClassesGenerator();
    }

    public function build($namespace = "Rad\\Datas", $path = null) {
        $this->classesGenerator->namespace = $namespace;
        if ($path === null) {
            $this->classesGenerator->path = __DIR__ . '/../Datas/';
        } else {
            $this->classesGenerator->path = $path;
        }
        $tables = GeneratorTools::generateArrayTables();
        $this->generateClass($tables);

        return "Generated";
    }

    private function generateClass(array $tables) {
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
        }
    }

}
