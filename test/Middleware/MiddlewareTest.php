<?php

declare(strict_types=1);

namespace Rad\Test\Middleware;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface as PsrMiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rad\Error\Http\InternalErrorException;
use Rad\Http\Response;
use Rad\Middleware\Base\Produce;
use Rad\Middleware\ControllerHandler;
use Rad\Middleware\Dispatcher;
use Rad\Middleware\LegacyMiddlewareAdapter;
use Rad\Middleware\MiddlewareAfter;
use Rad\Route\Route;
use Rad\Test\Fixtures\EchoController;

final class MiddlewareTest extends TestCase {
    private function request(): ServerRequestInterface {
        return new ServerRequest('GET', '/');
    }

    private function core(string $body = 'core'): RequestHandlerInterface {
        return new class ($body) implements RequestHandlerInterface {
            public function __construct(private string $body) {

            }

            public function handle(ServerRequestInterface $request): ResponseInterface {
                $response = new Response(200);
                $response->getBody()->write($this->body);
                return $response;
            }
        };
    }

    /** A PSR-15 middleware that adds a response header after handling. */
    private function headerMiddleware(string $name, string $value): PsrMiddlewareInterface {
        return new class ($name, $value) implements PsrMiddlewareInterface {
            public function __construct(private string $name, private string $value) {

            }

            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
                return $handler->handle($request)->withHeader($this->name, $this->value);
            }
        };
    }

    public function testDispatcherReachesCore(): void {
        $dispatcher = new Dispatcher([], $this->core('reached'));
        $response   = $dispatcher->handle($this->request());
        $this->assertSame('reached', (string) $response->getBody());
    }

    public function testMiddlewaresRunAroundCore(): void {
        $dispatcher = new Dispatcher(
            [$this->headerMiddleware('X-A', '1'), $this->headerMiddleware('X-B', '2')],
            $this->core()
        );
        $response = $dispatcher->handle($this->request());
        $this->assertSame('1', $response->getHeaderLine('X-A'));
        $this->assertSame('2', $response->getHeaderLine('X-B'));
    }

    public function testMiddlewareCanShortCircuit(): void {
        $shortCircuit = new class () implements PsrMiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
                return new Response(418);
            }
        };
        // Core would throw if reached.
        $core = new class () implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface {
                throw new \RuntimeException('core must not run');
            }
        };
        $dispatcher = new Dispatcher([$shortCircuit], $core);
        $this->assertSame(418, $dispatcher->handle($this->request())->getStatusCode());
    }

    public function testLegacyMiddlewareAdapterAfter(): void {
        $legacy = new class () extends MiddlewareAfter {
            public function middle(ServerRequestInterface $request, ResponseInterface $response, Route $route): ResponseInterface {
                return $response->withHeader('X-Legacy', 'yes');
            }
        };
        $dispatcher = new Dispatcher([new LegacyMiddlewareAdapter($legacy)], $this->core('body'));
        $response   = $dispatcher->handle($this->request()->withAttribute(Route::class, new Route()));
        $this->assertSame('yes', $response->getHeaderLine('X-Legacy'));
        $this->assertSame('body', (string) $response->getBody());
    }

    public function testControllerHandlerInvokesAction(): void {
        $route    = (new Route())->setClassName(EchoController::class)->setMethodName('index')->setMethod('GET')->setPath('/');
        $handler  = new ControllerHandler();
        $response = $handler->handle($this->request()->withAttribute(Route::class, $route));
        $this->assertSame('ok', (string) $response->getBody());
    }

    public function testControllerHandlerWithoutRouteThrows(): void {
        $this->expectException(InternalErrorException::class);
        (new ControllerHandler())->handle($this->request());
    }

    public function testProduceMiddlewareSetsContentType(): void {
        $route = (new Route())->setClassName(EchoController::class)->setMethodName('index')->setMethod('GET')->setPath('/');
        $route->setProduce(['json']);
        $dispatcher = new Dispatcher([new Produce()], new ControllerHandler());
        $response   = $dispatcher->handle($this->request()->withAttribute(Route::class, $route));
        $this->assertNotSame('', $response->getHeaderLine('Content-Type'));
    }
}
