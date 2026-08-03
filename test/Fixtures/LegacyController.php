<?php

declare(strict_types=1);

namespace Rad\Test\Fixtures;

use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rad\Controller\Controller;

/**
 * Controller still using legacy docblock annotations (backward-compat path).
 */
class LegacyController extends Controller {
    /**
     * @get /legacy/
     * @produce html
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        throw new LogicException('not executed');
    }
}
