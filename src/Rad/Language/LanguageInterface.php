<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Language;

/**
 * Description of LanguageInterface
 *
 * @author Guillaume Monet
 */
interface LanguageInterface {

    public function getText(string $value): ?string;
}
