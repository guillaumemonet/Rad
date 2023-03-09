<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Http;

use GuzzleHttp\Psr7\Request as GRequest;

/**
 * Description of Request.
 *
 * @author Admin
 */
class Request extends GRequest {

    use RequestTrait;
}
