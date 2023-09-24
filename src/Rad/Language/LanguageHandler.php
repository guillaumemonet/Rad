<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Language;

use Rad\Config\Config;

/**
 * Description of LanguageHandler
 *
 * @author Guillaume Monet
 */
class LanguageHandler implements LanguageInterface {

    private $default_locale;
    private $available_locales;
    private $config;
    private $trads;

    public function __construct() {
        $this->default_locale    = Config::getServiceConfig("language")->default_locale;
        $this->available_locales = Config::getServiceConfig("language")->available_locales;
        $this->config            = Config::getServiceConfig('language', 'language')->config;
        $this->loadTrads();
        register_shutdown_function(array($this, "saveTrads"));
    }

    private function loadTrads() {
        $this->trads = json_decode(file_get_contents(Config::getApiConfig()->install_path . $this->config->locales_path), true);
    }

    public function saveTrads() {
        file_put_contents(Config::getApiConfig()->install_path . $this->config->locales_path, json_encode((array) $this->trads, JSON_PRETTY_PRINT));
    }

    public function getText(string $value, $locale = null): ?string {
        $locale = $locale ?? $this->default_locale;
        if (!isset($this->trads[$value][$locale])) {
            foreach ($this->available_locales as $loc) {
                if (!isset($this->trads[$value][$loc])) {
                    $this->trads[$value][$loc] = $value;
                }
            }
        }
        return $this->trads[$value][$locale];
    }

    public function setLocale($locale) {
        putenv("LC_ALL=$locale");
        setlocale(LC_ALL, $locale);
    }
}
