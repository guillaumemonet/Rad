<?php

declare(strict_types=1);

namespace Rad\Test\Template;

use PHPUnit\Framework\TestCase;
use Rad\Config\Config;
use Rad\Template\TwigTemplateHandler;

final class TwigTemplateHandlerTest extends TestCase {
    protected function setUp(): void {
        Config::load();
        $cfg                                                           = Config::getConfig();
        $cfg->api->install_path                                        = __DIR__ . '/../Fixtures/twig/';
        $cfg->services->template->handlers->twig->config->template_dir = '';
        $cfg->services->template->handlers->twig->config->cache_dir    = '';
    }

    public function testRendersWithAssignedVars(): void {
        $handler = new TwigTemplateHandler();
        $handler->assign('name', 'World');
        $this->assertSame('Hello World', trim($handler->fetch('hello.twig')));
    }
}
