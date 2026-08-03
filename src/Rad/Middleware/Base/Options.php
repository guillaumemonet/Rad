<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Middleware\Base;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rad\Config\Config;
use Rad\Http\Response;

/**
 * Short-circuits pre-flight OPTIONS requests with a 200 response carrying the
 * configured CORS headers; passes everything else through.
 */
class Options extends AbstractMiddleware {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        if (strtoupper($request->getMethod()) !== 'OPTIONS') {
            return $handler->handle($request);
        }
        $response = new Response(200);
        foreach ((array) Config::getApiConfig('cors') as $header => $value) {
            $response = $response->withAddedHeader($header, $value);
        }
        return $response;
    }
}
