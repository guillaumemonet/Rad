<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Build\DatabaseBuilder;

use Nette\PhpGenerator\ClassType;
use Rad\Build\DatabaseBuilder\Elements\Table;

/**
 * Description of ClassesGeneratorStaticTrait
 *
 * @author Guillaume Monet
 */
trait ClassesGeneratorStaticTrait {

    public function generateIndexesGetter(ClassType $class, Table $table) {
        foreach ($table->indexes as $k => $i) {
            if ($i->name == "PRIMARY") {
                $this->generatePrimaryIndexGetter($class, $k, $i);
            } else if ($i->unique) {
                $this->generateUniqueIndexGetter($class, $table, $k, $i);
            } else {
                $this->generateOtherIndexGetter($class, $table, $k, $i);
            }
        }
    }

    public function generatePrimaryIndexGetter(ClassType $class, $k, $i) {
        $parse = $class->addMethod('get' . $class->getName())
                ->setStatic();
        $parse->setVisibility('public');

        foreach ($i->getColumns("name") as $param) {
            $parse->addParameter($param);
        }

        $parse->addBody('$c = new ' . $class->getName() . '(' . implode(",", $i->getColumns("php")) . ');');
        $parse->addBody('$c->read();');
        $parse->addBody('return $c;');
    }

    public function generateUniqueIndexGetter(ClassType $class, Table $table, $k, $i) {
        $parse = $class->addMethod($k)->setStatic();
        $parse->setVisibility('public');

        foreach ($i->getColumns("name") as $param) {
            $parse->addParameter($param);
        }
        $parse->addBody("\$c_key = \"cache_" . $k . "_\"." . implode('."_".', $i->getColumns("php")) . ";");
        $parse->addBody("\$" . $table->name . " = unserialize(Cache::getHandler()->get(\$c_key));");
        $parse->addBody("if(\$" . $table->name . " === false){");
        $parse->addBody("\$sql = \"SELECT * FROM " . $table->name . " WHERE " . implode(" AND ", $i->getColumns("sql_cond")) . (isset($table->columns["trash"]) ? " AND `trash`=0 " : "") . "\";");
        $parse->addBody("\$result = Database::getHandler()->prepare(\$sql);");
        $parse->addBody("\$result->execute(array(" . implode(",", $i->getColumns("bind")) . "));");
        $parse->addBody("\$row = \$result->fetch(\PDO::FETCH_ASSOC);");
        $parse->addBody("if(is_array(\$row) && count(\$row) > 0){");
        $parse->addBody("\$$table->name = new " . $class->getName() . "();");
        $parse->addBody("\$" . $table->name . "->parse(\$row);");
        $parse->addBody("Cache::getHandler()->set(\$c_key,serialize(\$" . $table->name . "));");
        $parse->addBody("}");
        $parse->addBody("}");
        $parse->addBody("return \$$table->name;");
    }

    public function generateOtherIndexGetter(ClassType $class, Table $table, $k, $i) {

        $parse = $class->addMethod($k)->setStatic();
        $parse->setVisibility('public');

        foreach ($i->getColumns("name") as $param) {
            $parse->addParameter($param);
        }
        $parse->addParameter('filters');
        $parse->addParameter("offset")->setDefaultValue(null);
        $parse->addParameter("limit")->setDefaultValue(null);
        $parse->addBody(' $infos_filters = static::getSQLFilters($filters);');
        $parse->addBody("\$c_key = \"cache_" . $k . "_\"." . implode('."_".', $i->getColumns("php")) . ".\$limit.\"_\".\$offset. \"_\" . \$infos_filters['md5'];");
        $parse->addBody("\$ret = unserialize(Cache::getHandler()->get(\$c_key));");
        $parse->addBody("if(\$ret === false){");
        $parse->addBody("\$sql = \"SELECT * FROM " . $table->name . " WHERE " . implode(" AND ", $i->getColumns("sql_cond")) . "\" . \$infos_filters['sql'] . (\$offset !==null && \$limit !== null?\" LIMIT \$offset,\$limit\":\"\").\"\";");
        $parse->addBody("\$result = Database::getHandler()->prepare(\$sql);");
        $parse->addBody("\$result->execute(array(" . implode(",", $i->getColumns("bind")) . ")+\$infos_filters['bind']);");
        $parse->addBody("\$ret = [];");
        $parse->addBody("while(\$row = \$result->fetch(\PDO::FETCH_ASSOC)){");
        $parse->addBody("\$" . $table->name . " = new " . $class->getName() . "();");
        $parse->addBody("\$" . $table->name . "->parse(\$row);");
        $parse->addBody("\$ret[] = \$" . $table->name . ";");
        $parse->addBody("}");
        $parse->addBody("Cache::getHandler()->set(\$c_key,serialize(\$ret));");
        $parse->addBody("}");
        $parse->addBody("return \$ret;");
    }

    public function generateGetAll(ClassType $class, Table $table) {

        $parse = $class->addMethod('getAll')->setStatic();
        $parse->setVisibility('public');
        $parse->addParameter('filters');
        $parse->addParameter('offset');
        $parse->addParameter('limit');
        $parse->addBody(' $infos_filters = static::getSQLFilters($filters);');
        $parse->addBody("\$c_key = \"key_" . $class->getName() . "_all_" . $table->name . "_\".\$limit.\"_\".\$offset. \"_\" . \$infos_filters['md5'];");
        $parse->addBody("\$ret = unserialize(Cache::getHandler()->get(\$c_key));");
        $parse->addBody("if(\$ret === false){");
        $parse->addBody("\$sql = \"SELECT * FROM " . $table->name . " WHERE 1 \" . \$infos_filters['sql'] . (\$offset !==null && \$limit !== null ?\" LIMIT \$offset,\$limit\":\"\").\"\";");
        $parse->addBody("\$result = Database::getHandler()->prepare(\$sql);");
        $parse->addBody("\$result->execute(\$infos_filters['bind']);");
        $parse->addBody("\$ret = [];");
        $parse->addBody("while(\$row = \$result->fetch(\PDO::FETCH_ASSOC)){");
        $parse->addBody("\$$table->name = new " . $class->getName() . ";");
        $parse->addBody("\$" . $table->name . "->parse(\$row);");
        $parse->addBody("\$ret[] = \$$table->name;");
        $parse->addBody("}");
        $parse->addBody("Cache::getHandler()->set(\$c_key,serialize(\$ret));");
        $parse->addBody("}");
        $parse->addBody("return \$ret;");
    }

}
