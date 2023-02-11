<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Database;

use PDO;
use PDOStatement;

/**
 * Description of DatabaseAdapter
 *
 * @author guillaume
 */
abstract class DatabaseAdapter extends PDO {

    public abstract function fetch_assoc(PDOStatement $rid);
}
