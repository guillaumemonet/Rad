<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @package rad-framework
 */

namespace Rad\Observer;

/**
 * Description of Observer
 *
 */
interface Observer {

    public function update(Observable $observable);
}
