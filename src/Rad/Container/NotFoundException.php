<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Container;

use Psr\Container\NotFoundExceptionInterface;
use Rad\Error\ContainerException;

/**
 * Thrown when the container cannot find an entry for the given identifier.
 */
class NotFoundException extends ContainerException implements NotFoundExceptionInterface {
}
