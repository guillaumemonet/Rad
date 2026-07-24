<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * PSR-15 middleware dispatcher (a request handler that runs a queue of
 * middlewares and finally delegates to a fallback handler — the "core").
 *
 * The dispatcher is immutable while running: each step clones itself with an
 * advanced cursor, so it is safe to re-enter.
 */
final class Dispatcher implements RequestHandlerInterface {

    /** @var MiddlewareInterface[] */
    private array $queue;

    private RequestHandlerInterface $core;

    private int $index;

    /**
     * @param MiddlewareInterface[] $queue
     */
    public function __construct(array $queue, RequestHandlerInterface $core, int $index = 0) {
        $this->queue = array_values($queue);
        $this->core  = $core;
        $this->index = $index;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        if (!isset($this->queue[$this->index])) {
            return $this->core->handle($request);
        }
        $middleware = $this->queue[$this->index];
        return $middleware->process($request, new self($this->queue, $this->core, $this->index + 1));
    }
}
