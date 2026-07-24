<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Container;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Rad\Build\Build;
use Rad\Build\BuildInterface;
use Rad\Cache\Cache;
use Rad\Cache\CacheInterface;
use Rad\ClientApi\ClientApi;
use Rad\ClientApi\ClientApiInterface;
use Rad\Codec\Codec;
use Rad\Codec\CodecInterface;
use Rad\Config\Config;
use Rad\Cookie\Cookie;
use Rad\Cookie\CookieInterface;
use Rad\Database\Database;
use Rad\Database\DatabaseAdapter;
use Rad\Encryption\Encryption;
use Rad\Encryption\EncryptionInterface;
use Rad\Event\Event;
use Rad\Language\Language;
use Rad\Language\LanguageInterface;
use Rad\Log\Log;
use Rad\Mail\Mail;
use Rad\Mail\MailInterface;
use Rad\Session\Session;
use Rad\Session\SessionInterface;
use Rad\Template\Template;
use Rad\Template\TemplateInterface;

/**
 * Bridges the legacy static service facades (Log, Cache, Database, ...) into
 * the container so their interfaces can be injected/autowired.
 *
 * Bindings are NOT shared: each resolution delegates to the facade's
 * getHandler(), which keeps a single source of truth and honours any handler
 * swapped in at runtime via addHandler().
 */
final class ServiceProvider {

    private function __construct() {

    }

    /**
     * Config service type => [interface/base class => facade factory, ...].
     *
     * Grouping by service type lets us skip interfaces whose service is not
     * configured, so Container::has() stays truthful.
     *
     * @return array<string, array<class-string, \Closure>>
     */
    private static function map(): array {
        return [
            'log'       => [
                LoggerInterface::class => static fn() => Log::getHandler(),
                AbstractLogger::class  => static fn() => Log::getHandler(),
            ],
            'cache'     => [CacheInterface::class => static fn() => Cache::getHandler()],
            'database'  => [DatabaseAdapter::class => static fn() => Database::getHandler()],
            'session'   => [SessionInterface::class => static fn() => Session::getHandler()],
            'cookie'    => [CookieInterface::class => static fn() => Cookie::getHandler()],
            'encrypt'   => [EncryptionInterface::class => static fn() => Encryption::getHandler()],
            'template'  => [TemplateInterface::class => static fn() => Template::getHandler()],
            'language'  => [LanguageInterface::class => static fn() => Language::getHandler()],
            'codec'     => [CodecInterface::class => static fn() => Codec::getHandler()],
            'build'     => [BuildInterface::class => static fn() => Build::getHandler()],
            'clientapi' => [ClientApiInterface::class => static fn() => ClientApi::getHandler()],
            'mail'      => [MailInterface::class => static fn() => Mail::getHandler()],
            'event'     => [EventDispatcherInterface::class => static fn() => Event::getHandler()],
        ];
    }

    /**
     * Register every configured framework service on the given container.
     */
    public static function register(Container $container): void {
        $services = Config::getConfig()->services ?? null;
        foreach (self::map() as $type => $bindings) {
            if ($services === null || !isset($services->{$type})) {
                continue;
            }
            foreach ($bindings as $id => $factory) {
                $container->bind($id, $factory);
            }
        }
    }
}
