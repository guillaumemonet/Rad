<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Route;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Description of RouterInterface
 *
 * @author guillaume
 */
interface RouterInterface {

    public function addGetRoute(Route $route): self;

    public function addPostRoute(Route $route): self;

    public function addPutRoute(Route $route): self;

    public function addPatchRoute(Route $route): self;

    public function addDeleteRoute(Route $route): self;

    public function addOptionsRoute(Route $route): self;

    public function mapRoute(string $method, Route $route): self;

    public function setRoutes(array $routes): self;

    public function route(ServerRequestInterface $request): ResponseInterface;

    public function load(array $controllers): self;

    public function save(): self;
}
