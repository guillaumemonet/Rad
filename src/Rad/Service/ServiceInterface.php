<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Service;

/**
 * Description of ServiceInterface
 *
 * @author guillaume
 */
interface ServiceInterface {
    public static function getHandler(string $handlerType = null);

    public static function addHandler(string $handlerType, $handler);
}
