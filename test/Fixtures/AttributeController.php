<?php

declare(strict_types=1);

namespace Rad\Test\Fixtures;

use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rad\Controller\Controller;
use Rad\Route\Attribute\Get;
use Rad\Route\Attribute\Post;
use Rad\Route\Attribute\Produce;
use Rad\Route\Attribute\Version;

/**
 * Controller declaring its routes with PHP 8 attributes (never executed in tests).
 */
class AttributeController extends Controller {

    #[Get('/attr/'), Produce('html')]
    public function index(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        throw new LogicException('not executed');
    }

    #[Version(2), Post('/attr/create/'), Produce('json')]
    public function create(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        throw new LogicException('not executed');
    }
}
