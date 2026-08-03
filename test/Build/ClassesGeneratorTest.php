<?php

declare(strict_types=1);

namespace Rad\Test\Build;

use Nette\PhpGenerator\ClassType;
use PHPUnit\Framework\TestCase;
use Rad\Build\DatabaseBuilder\ClassesGenerator;
use Rad\Build\DatabaseBuilder\Elements\Column;
use Rad\Build\DatabaseBuilder\Elements\Table;

final class ClassesGeneratorTest extends TestCase {
    private function tableWithI18n(): Table {
        $id           = new Column();
        $id->name     = 'id';
        $id->key      = 'PRI';
        $id->auto     = 1;
        $id->type_php = 'int';
        $id->type_sql = 'int';

        $i18n           = new Column();
        $i18n->name     = 'name_i18n';
        $i18n->key      = '';
        $i18n->auto     = 0;
        $i18n->type_php = 'int';
        $i18n->type_sql = 'int';

        $table          = new Table();
        $table->name    = 'article';
        $table->columns = ['id' => $id, 'name_i18n' => $i18n];
        return $table;
    }

    public function testUpdateUpsertsTranslations(): void {
        $generator = new ClassesGenerator();
        $class     = new ClassType('Article');
        $generator->generateUpdate($class, $this->tableWithI18n());
        $code = (string) $class;

        // Container created when missing.
        $this->assertStringContainsString('if($this->name_i18n == null)', $code);
        $this->assertStringContainsString('$li18n = new I18n()', $code);
        // Existing container: fetch, then create-if-missing / update-otherwise.
        $this->assertStringContainsString('I18nTranslate::getTranslation($this->name_i18n,$lang)', $code);
        $this->assertStringContainsString('if($ti18n === false || $ti18n === null)', $code);
        $this->assertStringContainsString('$ti18n = new I18nTranslate()', $code);
        $this->assertStringContainsString('$ti18n->update()', $code);
    }

    public function testCreateBuildsContainerAndTranslations(): void {
        $generator = new ClassesGenerator();
        $class     = new ClassType('Article');
        $generator->generateCreate($class, $this->tableWithI18n());
        $code = (string) $class;

        $this->assertStringContainsString('if($this->name_i18n == null)', $code);
        $this->assertStringContainsString('$li18n = new I18n()', $code);
        $this->assertStringContainsString('$ti18n->create()', $code);
    }
}
