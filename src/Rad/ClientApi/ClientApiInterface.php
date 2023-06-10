<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\ClientApi;

/**
 * Description of SessionInterface
 *
 * @author guillaume
 */
interface ClientApiInterface {

    public function call(string $endpoint, array $get = null, array $post = null, array $header = null);
}
