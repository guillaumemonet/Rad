<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Http;

use GuzzleHttp\Psr7\HttpFactory as GuzzleHttpFactory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * Single PSR-17 factory for the framework, delegating to a concrete PSR-7
 * implementation.
 *
 * This is the ONLY place the concrete implementation (Guzzle) is referenced:
 * swap the inner factory to change implementation (e.g. nyholm/psr7).
 */
final class HttpFactory implements
    RequestFactoryInterface,
    ResponseFactoryInterface,
    ServerRequestFactoryInterface,
    StreamFactoryInterface,
    UploadedFileFactoryInterface,
    UriFactoryInterface {
    private GuzzleHttpFactory $factory;

    public function __construct() {
        $this->factory = new GuzzleHttpFactory();
    }

    public function createRequest(string $method, $uri): RequestInterface {
        return $this->factory->createRequest($method, $uri);
    }

    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface {
        return $this->factory->createResponse($code, $reasonPhrase);
    }

    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface {
        return $this->factory->createServerRequest($method, $uri, $serverParams);
    }

    public function createStream(string $content = ''): StreamInterface {
        return $this->factory->createStream($content);
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface {
        return $this->factory->createStreamFromFile($filename, $mode);
    }

    public function createStreamFromResource($resource): StreamInterface {
        return $this->factory->createStreamFromResource($resource);
    }

    public function createUploadedFile(
        StreamInterface $stream,
        ?int $size = null,
        int $error = \UPLOAD_ERR_OK,
        ?string $clientFilename = null,
        ?string $clientMediaType = null
    ): UploadedFileInterface {
        return $this->factory->createUploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
    }

    public function createUri(string $uri = ''): UriInterface {
        return $this->factory->createUri($uri);
    }
}
