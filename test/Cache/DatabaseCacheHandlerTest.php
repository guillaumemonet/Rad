<?php

declare(strict_types=1);

namespace Rad\Test\Cache;

use Rad\Cache\CacheInterface;
use Rad\Cache\DatabaseCacheHandler;
use Rad\Config\Config;
use Rad\Database\Database;
use Throwable;

final class DatabaseCacheHandlerTest extends CacheContractTestCase {
    protected function createHandler(): CacheInterface {
        if (!extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('ext-pdo_mysql is not installed');
        }
        Config::load();
        $db           = Config::getConfig()->services->database->handlers->pdo->config;
        $db->type     = 'mysql';
        $db->host     = getenv('MYSQL_HOST') ?: '127.0.0.1';
        $db->database = getenv('MYSQL_DATABASE') ?: 'rad';
        $db->user     = getenv('MYSQL_USER') ?: 'rad';
        $db->password = getenv('MYSQL_PASSWORD') ?: 'rad';
        try {
            Database::getHandler('pdo')->exec(
                'CREATE TABLE IF NOT EXISTS output_cache ('
                    . '`id` CHAR(40) NOT NULL, `modified` INT, `content` LONGTEXT NOT NULL,'
                    . ' PRIMARY KEY (`id`), INDEX(`modified`)) ENGINE=InnoDB'
            );
        } catch (Throwable $ex) {
            $this->markTestSkipped('MySQL unavailable: ' . $ex->getMessage());
        }
        return new DatabaseCacheHandler();
    }
}
