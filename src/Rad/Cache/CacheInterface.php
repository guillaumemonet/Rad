<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Cache;

use Psr\SimpleCache\CacheInterface as PsrCacheInterface;

/**
 * Description of CacheInterface
 *
 * @author Guillaume Monet
 */
interface CacheInterface extends PsrCacheInterface {

    public function purge(): bool;
}
