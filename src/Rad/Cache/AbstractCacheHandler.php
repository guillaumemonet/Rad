<?php

declare(strict_types=1);

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Cache;

use DateInterval;
use DateTimeImmutable;

/**
 * Shared plumbing for cache handlers implementing the PSR-16 (SimpleCache)
 * interface with native types.
 */
abstract class AbstractCacheHandler implements CacheInterface {
    /**
     * Normalize a PSR-16 TTL (seconds or DateInterval) to a number of seconds.
     */
    protected function ttlToSeconds(null|int|DateInterval $ttl): ?int {
        if ($ttl instanceof DateInterval) {
            $now = new DateTimeImmutable();
            return $now->add($ttl)->getTimestamp() - $now->getTimestamp();
        }
        return $ttl;
    }
}
