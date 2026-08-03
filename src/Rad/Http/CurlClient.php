<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Http;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Rad\Http\Exception\NetworkException;

/**
 * Minimal PSR-18 HTTP client backed by cURL.
 */
final class CurlClient implements ClientInterface {
    /**
     * @param array<int, mixed> $options extra curl_setopt options
     */
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory,
        private array $options = []
    ) {
    }

    public function sendRequest(RequestInterface $request): ResponseInterface {
        $ch = curl_init();

        $statusCode      = 200;
        $reasonPhrase    = '';
        $responseHeaders = [];

        curl_setopt_array($ch, [
            CURLOPT_URL            => (string) $request->getUri(),
            CURLOPT_CUSTOMREQUEST  => $request->getMethod(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => $this->flattenHeaders($request),
            CURLOPT_HEADERFUNCTION => function ($curl, string $line) use (&$statusCode, &$reasonPhrase, &$responseHeaders): int {
                $trimmed = trim($line);
                if ($trimmed === '') {
                    return strlen($line);
                }
                if (preg_match('#^HTTP/\S+\s+(\d{3})\s*(.*)$#', $trimmed, $matches)) {
                    // Reset on each status line to keep only the final response's headers.
                    $statusCode      = (int) $matches[1];
                    $reasonPhrase    = $matches[2];
                    $responseHeaders = [];
                } elseif (str_contains($trimmed, ':')) {
                    [$name, $value]                 = explode(':', $trimmed, 2);
                    $responseHeaders[trim($name)][] = trim($value);
                }
                return strlen($line);
            },
        ]);

        $protocolVersion = $request->getProtocolVersion();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, $protocolVersion === '2.0' ? CURL_HTTP_VERSION_2_0 : CURL_HTTP_VERSION_1_1);

        $body = (string) $request->getBody();
        if ($body !== '') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        foreach ($this->options as $option => $value) {
            curl_setopt($ch, $option, $value);
        }

        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new NetworkException($request, $error);
        }
        curl_close($ch);

        $response = $this->responseFactory->createResponse($statusCode, $reasonPhrase)
                ->withBody($this->streamFactory->createStream((string) $result));
        foreach ($responseHeaders as $name => $values) {
            foreach ($values as $value) {
                $response = $response->withAddedHeader($name, $value);
            }
        }
        return $response;
    }

    /**
     * @return string[]
     */
    private function flattenHeaders(RequestInterface $request): array {
        $headers = [];
        foreach ($request->getHeaders() as $name => $values) {
            $headers[] = $name . ': ' . implode(', ', $values);
        }
        return $headers;
    }
}
