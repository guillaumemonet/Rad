<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Route;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Description of RouterInterface
 *
 * @author guillaume
 */
interface RouterInterface {

    public function addGetRoute(Route $route);

    public function addPostRoute(Route $route);

    public function addPutRoute(Route $route);

    public function addPatchRoute(Route $route);

    public function addDeleteRoute(Route $route);

    public function addOptionsRoute(Route $route);

    public function mapRoute(string $method, Route $route);

    public function setRoutes(array $routes);

    public function route(ServerRequestInterface $request);

    public function load(): bool;

    public function save();
}
