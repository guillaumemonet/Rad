<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Language;

use Rad\Config\Config;
use Rad\Error\ServiceException;

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
        $this->setLocale();
        $this->loadTrads();
        register_shutdown_function(array($this, "saveTrads"));
    }

    public function loadTrads() {
        $filePath = Config::getApiConfig()->install_path . $this->config->locales_path;
        if (file_exists($filePath)) {
            $fileContents = file_get_contents($filePath);
            if ($fileContents === false) {
                throw new ServiceException("Erreur lors de la lecture du fichier de traduction.");
            }
            $this->trads = json_decode($fileContents, true);

            if ($this->trads === null) {
                throw new ServiceException("Erreur lors de la décodage JSON du fichier de traduction.");
            }
        } else {
            $this->trads = [];
        }
    }

    public function saveTrads() {
        $filePath = Config::getApiConfig()->install_path . $this->config->locales_path;

        // Conversion des données de traduction en JSON avec une indentation propre.
        $jsonData = json_encode((array) $this->trads, JSON_PRETTY_PRINT);

        if ($jsonData === false) {
            throw new ServiceException("Erreur lors de la conversion des données en JSON.");
        }

        // Tentative d'écriture dans le fichier.
        $writeResult = file_put_contents($filePath, $jsonData);

        if ($writeResult === false) {
            throw new ServiceException("Erreur lors de l'écriture dans le fichier de traduction.");
        }
    }

    public function getText(string $value, $domaine = "default", $locale = null): ?string {
        $locale = $locale ?? $this->default_locale;

        if (!isset($this->trads[$domaine][$value][$locale])) {
            if (!isset($this->trads[$domaine])) {
                $this->trads[$domaine] = [];
            }

            if (!isset($this->trads[$domaine][$value])) {
                $this->trads[$domaine][$value] = [];
            }

            foreach ($this->available_locales as $loc) {
                if (!isset($this->trads[$domaine][$value][$loc])) {
                    $this->trads[$domaine][$value][$loc] = $this->trads[$domaine][$value][$this->default_locale] ?? $value;
                }
            }
        }

        return $this->trads[$domaine][$value][$locale];
    }

    public function setLocale() {
        putenv("LC_ALL=" . $this->default_locale);
        setlocale(LC_ALL, $this->default_locale);
    }
}
