<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Build\DatabaseBuilder;

/**
 * Description of BaseGenerator
 *
 * @author Guillaume Monet
 */
class BaseGenerator {

    protected string $query   = "\$result = Database::getHandler()->query(\$sql)";
    protected string $prepare = "\$result = Database::getHandler()->prepare(\$sql)";
    protected string $execute = "\$result->execute(%s)";
    protected string $result  = "\$res = \$result->fetchAll(\PDO::FETCH_ASSOC)";

}
