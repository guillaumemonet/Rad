<?php

declare(strict_types=1);

namespace Rad\Test\Fixtures;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rad\Controller\Controller;

/**
 * Minimal controller returning a fixed body, used by the middleware pipeline tests.
 */
class EchoController extends Controller {
    public function index(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $response->getBody()->write('ok');
        return $response;
    }
}
