<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Model;

/**
 * Description of ModelInterface
 *
 * @author Guillaume Monet
 */
interface ModelInterface {

    public function create(bool $force = false);

    public function read();

    public function update();

    public function delete();
}
