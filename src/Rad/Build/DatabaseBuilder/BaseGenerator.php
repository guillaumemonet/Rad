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

    public $baseRequire = [
        "PDO",
        "Rad\\Model\\Model",
        "Rad\\Model\\ModelDAO",
        "Rad\\Database\\Database",
        "Rad\\Cache\\Cache",
        "Rad\\Log\\Log",
        "Rad\\Utils\\StringUtils",
        "Rad\\Encryption\\Encryption"
    ];
    
    protected string $query   = "\$result = Database::getHandler()->query(\$sql)";
    protected string $prepare = "\$result = Database::getHandler()->prepare(\$sql)";
    protected string $execute = "\$result->execute(%s)";
    protected string $result  = "\$res = \$result->fetchAll(\PDO::FETCH_ASSOC)";

}
