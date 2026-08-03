<?php

declare(strict_types=1);

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Template;

use Rad\Config\Config;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Twig implementation of the template service.
 *
 * Requires twig/twig. Configure it under services.template.handlers.twig, e.g.:
 *   { "template_dir": "templates/", "cache_dir": "cache/twig/",
 *     "debug": true, "auto_reload": true }
 */
class TwigTemplateHandler implements TemplateInterface {
    private Environment $twig;

    /** @var array<string, mixed> */
    private array $vars = [];

    public function __construct() {
        $config      = Config::getServiceConfig('template', 'twig')->config;
        $installPath = Config::getApiConfig('install_path') ?? '';
        $loader      = new FilesystemLoader(rtrim($installPath . ($config->template_dir ?? ''), '/'));

        $options = [
            'debug'       => (bool) ($config->debug ?? false),
            'auto_reload' => (bool) ($config->auto_reload ?? true),
        ];
        if (!empty($config->cache_dir)) {
            $options['cache'] = rtrim($installPath . $config->cache_dir, '/');
        }
        $this->twig = new Environment($loader, $options);
    }

    public function assign($varname, $value, $nocache = false) {
        $this->vars[$varname] = $value;
        return $this;
    }

    public function fetch($filename = null, $cache_id = null, $compile_id = null, $parent = null) {
        return $this->twig->render((string) $filename, $this->vars);
    }

    public function display($filename = null, $cache_id = null, $compile_id = null, $parent = null) {
        echo $this->fetch($filename, $cache_id, $compile_id, $parent);
    }

    public function isCached($filename = null, $cache_id = null, $compile_id = null, $parent = null) {
        // Twig manages its own compilation cache transparently.
        return false;
    }

    /**
     * Direct access to the Twig environment (to register extensions, filters...).
     */
    public function getEnvironment(): Environment {
        return $this->twig;
    }
}
