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

    /**
     * 
     * @param string $endpoint
     * @param array $get
     * @param array $post
     * @param array $header
     * @param bool $caching
     */
    public function call(string $endpoint, array $get = null, array $post = null, array $header = [], bool $caching = true);
}
