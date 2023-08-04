<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Model;

use Exception;
use Nette\PhpGenerator\PhpNamespace;
use PDO;
use Rad\Database\Database;
use Rad\Utils\StringUtils;
use ReflectionClass;

/**
 * Description of ModelDatabase
 *
 * @author Guillaume Monet
 */
class ModelDatabase {

    // Méthode pour enregistrer les modifications de la structure de la table dans la base de données
    public static function saveStructure(string $class_name) {
        // Vérifier si la classe existe
        if (!class_exists($class_name)) {
            throw new Exception("La classe $class_name n'existe pas.");
        }

        // Récupérer les informations sur les attributs depuis les commentaires de la classe
        $class_attributes = self::getClassAttributes($class_name);

        // Séparer les nouvelles colonnes, indexes et foreign keys des attributs de classe
        $table_name   = $class_attributes['table'];
        $columns      = $class_attributes['columns'];
        $indexes      = $class_attributes['indexes'];
        $foreign_keys = $class_attributes['foreign_keys'];

        self::createTableIfNotExists($table_name, $columns, $indexes['primary'], $indexes);
        // Récupérer les informations actuelles sur la table depuis la base de données
        $existing_columns      = self::getTableColumns($table_name);
        $existing_indexes      = self::getTableIndexes($table_name);
        $existing_foreign_keys = self::getTableForeignKeys($table_name);

        qdh($existing_columns);
        qdh($columns);

        // Mettre à jour la structure de la table
        self::updateTableColumns($table_name, $columns, $existing_columns);
        self::updateTableIndexes($table_name, $indexes, $existing_indexes);
        self::updateTableForeignKeys($table_name, $foreign_keys, $existing_foreign_keys);
    }

    private static function getClassAttributes(string $class_name): array {
        $reflectionClass  = new ReflectionClass($class_name);
        $class_attributes = [
            'table'        => '',
            'columns'      => [],
            'indexes'      => ['primary' => [], 'unique' => [], 'key' => []],
            'foreign_keys' => []
        ];

        // Parcourir les attributs de la classe
        foreach ($reflectionClass->getProperties() as $property) {
            $docComment = $property->getDocComment();
            if ($docComment !== false) {
                // Utiliser StringUtils::parseComments pour analyser les annotations du commentaire
                $attributes  = StringUtils::parseComments($docComment);
                $column_name = $property->getName();

                //Avec un fallback si type n'est pas setter on met
                if (isset($attributes['var'])) {
                    $php_type                                          = $attributes['var'][0];
                    // Utiliser mapPhpTypeToSqlType pour obtenir le type SQL correspondant au type PHP
                    $sql_type                                          = self::mapPhpTypeToSqlType($php_type);
                    $class_attributes['columns'][$column_name]['var']  = $php_type;
                    $class_attributes['columns'][$column_name]['type'] = $sql_type;
                } else {
                    $php_type                                          = $property->getType()->getName();
                    $sql_type                                          = self::mapPhpTypeToSqlType($php_type);
                    $class_attributes['columns'][$column_name]['var']  = $php_type;
                    $class_attributes['columns'][$column_name]['type'] = $sql_type;
                }

                if (isset($attributes['type'])) {
                    $sql_type                                          = $attributes['type'][0];
                    $class_attributes['columns'][$column_name]['type'] = $sql_type;
                }

                // Vérifier si l'attribut fait partie de la clé primaire
                if (isset($attributes['pkey'])) {
                    $class_attributes['indexes']['primary'][] = $column_name;
                }

                // Vérifier si l'attribut fait partie d'un index unique
                if (isset($attributes['unique'])) {
                    $unique_index_name                                           = $attributes['unique'][0];
                    $class_attributes['indexes']['unique'][$unique_index_name][] = $column_name;
                }

                // Vérifier si l'attribut fait partie d'un index
                if (isset($attributes['index'])) {
                    $key_index_name                                        = $attributes['index'][0];
                    $class_attributes['indexes']['key'][$key_index_name][] = $column_name;
                }

                // Vérifier si l'attribut fait partie d'une clé étrangère
                if (isset($attributes['fk'])) {
                    $fk_info                            = self::parseForeignKeyAnnotation($attributes['fk'][0]);
                    $class_attributes['foreign_keys'][] = [
                        'name' => $fk_info['name'],
                        'from' => $column_name,
                        'to'   => $fk_info['to']
                    ];
                }

                // Récupérer les autres informations spécifiées dans les commentaires
                // comme @var, @length, @autoinc, @notnull, @default, etc.

                if (isset($attributes['length'])) {
                    $length                                              = $attributes['length'][0];
                    $class_attributes['columns'][$column_name]['length'] = $length;
                }

                if (isset($attributes['autoinc'])) {
                    $class_attributes['columns'][$column_name]['autoinc'] = true;
                }

                if (isset($attributes['notnull'])) {
                    $class_attributes['columns'][$column_name]['notnull'] = true;
                }

                if ($property->hasDefaultValue()) {
                    $class_attributes['columns'][$column_name]['default'] = $property->getDefaultValue();
                }
            }
        }
        $class_attributes['table'] = StringUtils::parseComments($reflectionClass->getDocComment())['table'][0];
        return $class_attributes;
    }

    // ...
    // Méthode pour analyser l'annotation @fk et extraire les informations sur la clé étrangère
    private static function parseForeignKeyAnnotation(string $annotation_content): array {
        $fk_info = [
            'name' => '', // Nom de la clé étrangère
            'to'   => [// Informations sur la colonne cible (clé primaire de la table de référence)
                'table'  => '',
                'column' => ''
            ]
        ];

        // Analyser le contenu de l'annotation @fk
        $parts = explode(' ', $annotation_content);
        if (count($parts) === 2) {
            // La structure de l'annotation doit être fkname[table=>column]
            $fk_info['name'] = $parts[0];
            $to_parts        = explode('=>', $parts[1]);
            if (count($to_parts) === 2) {
                $fk_info['to']['table']  = $to_parts[0];
                $fk_info['to']['column'] = $to_parts[1];
            }
        }

        return $fk_info;
    }

    private static function createTableIfNotExists(string $table_name, array $columns, array $primary_key, array $indexes) {
        $pdo = Database::getHandler();

        // Vérifier si la table existe déjà dans la base de données
        $query        = "SHOW TABLES LIKE '$table_name'";
        $stmt         = $pdo->prepare($query);
        $stmt->execute();
        $table_exists = $stmt->rowCount() > 0;

        if (!$table_exists) {
            // Création de la table avec les colonnes
            $query = "CREATE TABLE $table_name (";

            // Ajout des colonnes
            $column_definitions = [];
            foreach ($columns as $column_name => $column) {
                $column_sql           = self::getColumnDefinition($column_name, $column);
                $column_definitions[] = $column_sql;
            }

            // Ajout de la clé primaire si définie
            if (!empty($primary_key)) {
                $primary_key_columns  = implode(',', $primary_key);
                $column_definitions[] = "PRIMARY KEY ($primary_key_columns)";
            }

            // Ajout des indexes uniques
            foreach ($indexes['unique'] as $index_name => $columns) {
                $columns_list         = implode(',', $columns);
                $column_definitions[] = "UNIQUE INDEX $index_name ($columns_list)";
            }

            // Ajout des indexes non uniques (clés)
            foreach ($indexes['key'] as $index_name => $columns) {
                $columns_list         = implode(',', $columns);
                $column_definitions[] = "INDEX $index_name ($columns_list)";
            }

            $query .= implode(',', $column_definitions);
            $query .= ")";

            // Exécution de la requête pour créer la table
            $pdo->exec($query);
        }
    }

    // Méthode pour mettre à jour les colonnes de la table en fonction des changements détectés dans la classe
    private static function updateTableColumns(string $table_name, array $columns, array $existing_columns) {
        $pdo = Database::getHandler();

        // Vérifier si chaque nouvelle colonne doit être ajoutée à la table
        foreach ($columns as $column_name => $column_info) {
            if (!array_key_exists($column_name, $existing_columns)) {
                $column_definition = self::getColumnDefinition($column_name, $column_info);
                $sql               = "ALTER TABLE $table_name ADD $column_definition";
                $pdo->exec($sql);
            } else {
                // La colonne existe déjà, vérifier si des modifications sont nécessaires
                $existing_column_info = $existing_columns[$column_name];
                $modified_column_info = $column_info;

                // Si des changements ont été détectés, exécuter la requête SQL ALTER TABLE pour modifier la colonne
                if (self::compareColumns($existing_column_info, $modified_column_info)) {
                    $column_definition = self::getColumnDefinition($column_name, $modified_column_info);
                    $sql               = "ALTER TABLE $table_name MODIFY COLUMN $column_definition";
                    $pdo->exec($sql);
                }
            }
        }

        // Vérifier si des colonnes doivent être supprimées de la table
        foreach ($existing_columns as $column_name => $column_info) {
            if (!array_key_exists($column_name, $columns)) {
                $sql = "ALTER TABLE $table_name DROP COLUMN $column_name";
                $pdo->exec($sql);
            }
        }
    }

    private static function compareColumns(array $existing_column, array $modified_column): bool {
        $attributes_to_check = ['type', 'notnull', 'default', 'autoinc', 'length', 'var'];

        foreach ($attributes_to_check as $attribute) {
            $existing_value = array_key_exists($attribute, $existing_column) ? $existing_column[$attribute] : null;
            $modified_value = array_key_exists($attribute, $modified_column) ? $modified_column[$attribute] : null;

            if ($existing_value !== $modified_value) {
                return true;
            }
        }

        return false;
    }

    // Méthode pour mettre à jour les indexes de la table en fonction des changements détectés dans la classe
    private static function updateTableIndexes(string $table_name, array $indexes, array $existing_indexes) {
        $pdo = Database::getHandler();

        // Mettre à jour les indexes uniques
        $existing_unique_indexes = $existing_indexes['unique'];
        $new_unique_indexes      = $indexes['unique'];

        $indexes_to_add_unique    = array_diff_key($new_unique_indexes, $existing_unique_indexes);
        $indexes_to_remove_unique = array_diff_key($existing_unique_indexes, $new_unique_indexes);

        // Mettre à jour les indexes non uniques (clés)
        $existing_key_indexes = $existing_indexes['key'];
        $new_key_indexes      = $indexes['key'];

        $indexes_to_add_key    = array_diff_key($new_key_indexes, $existing_key_indexes);
        $indexes_to_remove_key = array_diff_key($existing_key_indexes, $new_key_indexes);

        foreach ($indexes_to_remove_unique as $index_name => $columns) {
            $sql = "ALTER TABLE $table_name DROP INDEX $index_name";
            $pdo->exec($sql);
        }

        foreach ($indexes_to_remove_key as $index_name => $columns) {
            $sql = "ALTER TABLE $table_name DROP INDEX $index_name";
            $pdo->exec($sql);
        }

        foreach ($indexes_to_add_unique as $index_name => $columns) {
            $sql = "ALTER TABLE $table_name ADD UNIQUE INDEX $index_name (" . implode(", ", $columns) . ")";
            $pdo->exec($sql);
        }

        foreach ($indexes_to_add_key as $index_name => $columns) {
            $sql = "ALTER TABLE $table_name ADD INDEX $index_name (" . implode(',', $columns) . ")";
            $pdo->exec($sql);
        }

        // Mettre à jour les colonnes des indexes existants
        foreach ($existing_indexes['unique'] as $index_name => $columns) {
            if (isset($new_unique_indexes[$index_name])) {
                $new_columns = $new_unique_indexes[$index_name];
                if ($columns !== $new_columns) {
                    $sql = "ALTER TABLE $table_name DROP INDEX $index_name, ADD UNIQUE INDEX $index_name (" . implode(", ", $new_columns) . ")";
                    $pdo->exec($sql);
                }
            }
        }

        foreach ($existing_indexes['key'] as $index_name => $columns) {
            if (isset($new_key_indexes[$index_name])) {
                $new_columns = $new_key_indexes[$index_name];
                if ($columns !== $new_columns) {
                    $sql = "ALTER TABLE $table_name DROP INDEX $index_name, ADD INDEX $index_name (" . implode(", ", $new_columns) . ")";
                    $pdo->exec($sql);
                }
            }
        }
    }

    // Méthode pour mettre à jour les clés étrangères de la table en fonction des changements détectés dans la classe
    private static function updateTableForeignKeys(string $table_name, array $foreign_keys, array $existing_foreign_keys) {
        $pdo = Database::getHandler();

        // Mettre à jour les clés étrangères
        $existing_fk_names = array_column($existing_foreign_keys, 'name');
        $new_fk_names      = array_column($foreign_keys, 'name');

        $fks_to_add    = array_diff($new_fk_names, $existing_fk_names);
        $fks_to_remove = array_diff($existing_fk_names, $new_fk_names);

        foreach ($fks_to_add as $fk_name) {
            $fk_info     = $foreign_keys[array_search($fk_name, $new_fk_names)];
            $from_column = $fk_info['from'];
            $to_table    = $fk_info['to']['table'];
            $to_column   = $fk_info['to']['column'];

            $sql = "ALTER TABLE $table_name ADD CONSTRAINT $fk_name FOREIGN KEY ($from_column) REFERENCES $to_table($to_column)";
            $pdo->exec($sql);
        }

        foreach ($fks_to_remove as $fk_name) {
            $sql = "ALTER TABLE $table_name DROP FOREIGN KEY $fk_name";
            $pdo->exec($sql);
        }
    }

    // Méthode pour récupérer les informations actuelles sur les colonnes de la table depuis la base de données
    private static function getTableColumns(string $table_name): array {
        $columns = [];
        // Récupérer les informations sur les colonnes depuis la base de données
        $sql     = "SHOW COLUMNS FROM $table_name";
        $result  = Database::getHandler()->query($sql);
        foreach ($result as $row) {
            $column_name    = $row['Field'];
            $type           = preg_replace('/\(.+\)/', '', $row['Type']);
            $notnull        = !($row['Null'] === 'YES');
            $default        = $row['Default'];
            $auto_increment = (strpos($row['Extra'], 'auto_increment') !== false);

            $columns[$column_name] = [
                'var'  => self::mapSqlTypeToPhpType($type),
                'type' => $type
            ];

            // Analyser le type SQL pour extraire la taille (pour les colonnes de type chaîne, double ou decimal)
            if (preg_match('/\((\d+)(,\d+)?\)/', $row['Type'], $matches)) {
                $columns[$column_name]['length'] = $matches[1] . (isset($matches[2]) ? $matches[2] : "");
            }

            // Ajouter les champs 'notnull', 'default' et 'autoinc' seulement si nécessaire
            if ($notnull) {
                $columns[$column_name]['notnull'] = true;
            }

            if ($default !== null) {
                $columns[$column_name]['default'] = $default;
            }

            if ($auto_increment) {
                $columns[$column_name]['autoinc'] = true;
            }
        }
        return $columns;
    }

    private static function getTableIndexes(string $table_name): array {
        $indexes = ['primary' => [], 'unique' => [], 'key' => []];
        // Récupérer les informations sur les indexes depuis la base de données
        $pdo     = Database::getHandler();

        $sql    = "SHOW CREATE TABLE $table_name";
        $stmt   = $pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (isset($result['Create Table'])) {
            $create_table_statement = $result['Create Table'];
            $pattern                = '/^\s*?(PRIMARY KEY|UNIQUE KEY|KEY)\s+(`\w+`)?\s*\(([^)]+)/m'; // Expression régulière


            if (preg_match_all($pattern, $create_table_statement, $matches, PREG_SET_ORDER, 0)) {
                foreach ($matches as $match) {
                    $index_type   = $match[1]; // Récupère le type
                    $nomOptionnel = isset($match[2]) ? trim($match[2], '`') : null; // Récupère le nom optionnel
                    $columns      = explode(',', $match[3]); // Récupère les colonnes et les place dans un tableau
                    $columns      = array_map(function ($item) {
                        return trim($item, '`');
                    }, $columns); // Supprime les espaces autour des noms de colonnes

                    if ($index_type === 'PRIMARY KEY') {
                        $indexes['primary'] = $columns;
                    } elseif ($index_type === 'UNIQUE KEY') {
                        $indexes['unique'][$nomOptionnel] = $columns;
                    } else {
                        $indexes['key'][$nomOptionnel] = $columns;
                    }
                }
            }
        }
        return $indexes;
    }

    // Méthode pour récupérer les informations actuelles sur les clés étrangères de la table depuis la base de données
    private static function getTableForeignKeys(string $table_name): array {
        $foreign_keys = [];
        // Récupérer les informations sur les clés étrangères depuis la base de données
        $sql          = "SHOW CREATE TABLE $table_name";
        $result       = Database::getHandler()->query($sql);
        if ($result) {
            $row                    = $result->fetch(PDO::FETCH_NUM);
            $create_table_statement = $row[1];
            $pattern                = '/FOREIGN KEY \(`(\w+)`\)\s+REFERENCES `(\w+)` \(`(\w+)`\)/';
            preg_match_all($pattern, $create_table_statement, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $fk_name        = $match[1];
                $from_column    = $match[2];
                $to_column      = $match[3];
                $foreign_keys[] = [
                    'name' => $fk_name,
                    'from' => $from_column,
                    'to'   => $to_column
                ];
            }
        }
        return $foreign_keys;
    }

    private static function getColumnDefinition(string $column_name, array $column_info): string {
        $column_definition = $column_name . ' ' . $column_info['type'];
        $nodefault = false;
        // Ajouter la taille si spécifiée dans les commentaires
        if (isset($column_info['length'])) {
            $column_definition .= '(' . $column_info['length'] . ')';
        }

        // Vérifier si la colonne est auto-incrémentée
        if (isset($column_info['autoinc']) && $column_info['autoinc'] === true) {
            $column_definition .= ' AUTO_INCREMENT';
            $nodefault = true;
        }

        // Vérifier si la colonne doit être non null
        if (isset($column_info['notnull']) && $column_info['notnull'] === true) {
            $column_definition .= ' NOT NULL';
        }

        // Ajouter la valeur par défaut si spécifiée
        if (!$nodefault && isset($column_info['default'])) {
            $default_value = $column_info['default'];

            // Vérifier si la valeur par défaut doit être entourée de guillemets
            if (is_string($default_value)) {
                $default_value = "'" . $default_value . "'";
            }

            $column_definition .= ' DEFAULT ' . $default_value;
        }
        return $column_definition;
    }

    private static function parseDocComment(string $doc_comment): array {
        $attributes = [];

        // Utiliser StringUtils::parseComments pour analyser les annotations
        $annotations = StringUtils::parseComments($doc_comment);

        // Traiter chaque annotation en fonction de son type
        foreach ($annotations as $annotation => $params) {
            switch ($annotation) {
                case 'var':
                    $attributes['var']     = $params[0];
                    break;
                case 'length':
                    $attributes['length']  = $params[0];
                    break;
                case 'pkey':
                    $attributes['pkey']    = true;
                    break;
                case 'autoinc':
                    $attributes['autoinc'] = true;
                    break;
                case 'notnull':
                    $attributes['notnull'] = true;
                    break;
                case 'unique':
                    $attributes['unique']  = $params;
                    break;
                case 'index':
                    $attributes['index']   = $params;
                    break;
                case 'fk':
                    $attributes['fk']      = self::parseForeignKeyAnnotation($params[0]);
                    break;
                default:
                    // Annotation inconnue, ignorer
                    break;
            }
        }

        return $attributes;
    }

// Méthode pour générer une classe PHP à partir de la structure d'une table de la base de données
    public static function loadStructure(string $table_name): string {
        // Récupérer les informations sur la structure de la table depuis la base de données
        $columns      = self::getTableColumns($table_name);
        $indexes      = self::getTableIndexes($table_name);
        $foreign_keys = self::getTableForeignKeys($table_name);

        // Générer le code de la classe
        $namespace = new PhpNamespace('App\\Models');
        $class     = $namespace->addClass(StringUtils::camelCase($table_name));

        // Générer les attributs de la classe en fonction des colonnes de la table
        foreach ($columns as $column_name => $column_info) {
            $type     = self::mapSqlTypeToPhpType($column_info['type']);
            $property = $class->addProperty($column_name)->setType($type);

            if (isset($column_info['notnull']) && $column_info['notnull']) {
                $property->addComment('@notnull');
            }

            if (isset($column_info['autoinc']) && $column_info['autoinc']) {
                $property->addComment('@autoinc');
            }

            if (isset($column_info['length'])) {
                $property->addComment('@length ' . $column_info['length']);
            }

            // Ajouter le type PHP
            $php_type = self::mapSqlTypeToPhpType($column_info['type']);
            $property->addComment('@var ' . $php_type);

            // Ajouter le type SQL
            $property->addComment('@type ' . $column_info['type']);

            if (in_array($column_name, $indexes['primary'])) {
                $property->addComment('@pkey');
            }

            if (isset($indexes['unique'])) {
                foreach ($indexes['unique'] as $index_name => $index_columns) {
                    if (in_array($column_name, $index_columns)) {
                        $property->addComment('@unique ' . $index_name);
                        break;
                    }
                }
            }

            if (isset($indexes['key'])) {
                foreach ($indexes['key'] as $index_name => $index_columns) {
                    if (in_array($column_name, $index_columns)) {
                        $property->addComment('@index ' . $index_name);
                        break;
                    }
                }
            }
        }

        // Générer les clés étrangères de la classe
        if (!empty($foreign_keys)) {
            foreach ($foreign_keys as $fk_info) {
                $fk_name   = $fk_info['name'];
                $from      = $fk_info['from'];
                $to_table  = $fk_info['to']['table'];
                $to_column = $fk_info['to']['column'];
                $property  = $class->addProperty($from)->setType('int'); // Changer 'int' selon le type de la clé étrangère
                $property->addComment("@fk $fk_name\[$to_table=>$to_column]");
            }
        }
        return $namespace;
    }

    private static function mapSqlTypeToPhpType(string $sql_type): string {
        $type_map = [
            'int'        => 'int',
            'tinyint'    => 'bool',
            'smallint'   => 'int',
            'mediumint'  => 'int',
            'bigint'     => 'int',
            'float'      => 'float',
            'double'     => 'float',
            'decimal'    => 'float',
            'date'       => '\DateTime',
            'time'       => '\DateTime',
            'datetime'   => '\DateTime',
            'timestamp'  => '\DateTime',
            'year'       => 'int',
            'char'       => 'string',
            'varchar'    => 'string',
            'text'       => 'string',
            'tinytext'   => 'string',
            'mediumtext' => 'string',
            'longtext'   => 'string',
            'enum'       => 'string',
            'set'        => 'string',
            'binary'     => 'string',
            'varbinary'  => 'string',
            'blob'       => 'string',
            'tinyblob'   => 'string',
            'mediumblob' => 'string',
            'longblob'   => 'string',
            'json'       => 'array',
            'jsonb'      => 'array',
        ];

        $sql_type = strtolower($sql_type);
        return $type_map[$sql_type] ?? 'mixed';
    }

    private static function mapPhpTypeToSqlType(string $php_type): string {
        $type_map = [
            'int'           => 'int',
            'integer'       => 'int',
            'float'         => 'float',
            'double'        => 'float',
            'string'        => 'varchar',
            'bool'          => 'tinyint',
            'boolean'       => 'tinyint',
            'array'         => 'json',
            'object'        => 'json',
            'json'          => 'json',
            'null'          => 'varchar',
            'resource'      => 'varchar',
            'callable'      => 'varchar',
            'iterable'      => 'json',
            'void'          => 'varchar',
            'mixed'         => 'varchar',
            'unknown'       => 'varchar',
            '\DateTime'     => 'datetime',
            '\DateInterval' => 'varchar',
        ];
        $php_type = strtolower($php_type);
        return $type_map[$php_type] ?? 'varchar';
    }

}
