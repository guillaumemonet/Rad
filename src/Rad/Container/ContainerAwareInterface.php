<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Container;

use Psr\Container\ContainerInterface;

/**
 * Implemented by components that can receive the application container.
 */
interface ContainerAwareInterface {

    public function setContainer(ContainerInterface $container): void;
}
