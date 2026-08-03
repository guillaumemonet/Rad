<?php

declare(strict_types=1);

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Utils;

/**
 * Type mapping helpers between PHP and SQL (used by the database model builder).
 */
abstract class SQLUtils {
    /**
     * Map a PHP type name to a reasonable SQL column type.
     */
    public static function mapPhpTypeToSqlType(string $phpType): string {
        return match (strtolower(ltrim($phpType, '?\\'))) {
            'int', 'integer'                => 'INT',
            'float', 'double'               => 'DOUBLE',
            'bool', 'boolean'               => 'TINYINT(1)',
            'datetime', 'datetimeimmutable' => 'DATETIME',
            'array', 'object'               => 'JSON',
            default                         => 'VARCHAR(255)',
        };
    }

    /**
     * Map an SQL column type (with or without size) to a PHP type name.
     */
    public static function mapSqlTypeToPhpType(string $sqlType): string {
        $base = strtolower((string) preg_replace('/\(.*/', '', trim($sqlType)));
        return match ($base) {
            'int', 'integer', 'tinyint', 'smallint', 'mediumint', 'bigint', 'year' => 'int',
            'float', 'double', 'decimal', 'dec', 'real'                            => 'float',
            'bool', 'boolean'                                                      => 'bool',
            default                                                                => 'string',
        };
    }
}
