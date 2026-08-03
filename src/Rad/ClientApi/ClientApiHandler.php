<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\ClientApi;

use Rad\Cache\Cache;
use Rad\Config\Config;
use Rad\Http\CurlClient;
use Rad\Http\HttpFactory;

/**
 * Description of ClientApiHandler
 *
 * @author guillaume
 */
class ClientApiHandler implements ClientApiInterface {
    /**
     * @param string $endpoint
     * @param array $get
     * @param array $post
     * @param array $headers
     * @param bool $caching
     */
    public function call(string $endpoint, array $get = null, array $post = null, array $headers = [], bool $caching = true) {
        $cfg     = Config::getServiceConfig('clientapi', 'rad')->config;
        $token   = $cfg->token;
        $cache   = boolval($cfg->cache_enabled) && $caching;
        $fullUrl = $cfg->url . $endpoint;

        $c_key = 'cache_clientapi_' . md5($fullUrl . $token);
        $datas = unserialize(Cache::getHandler()->get($c_key));
        if ($datas === false || !$cache) {
            $headers[] = 'Authorization: ' . $token;
            $datas     = $this->request($fullUrl, $get, $post, $headers);
            Cache::getHandler()->set($c_key, serialize($datas));
        }
        return $datas;
    }

    /**
     * Perform the HTTP call through the PSR-18 client and return the raw body.
     *
     * @param array<string, mixed>|null $get
     * @param array<string, mixed>|null $post
     * @param string[] $headers "Name: value" lines
     */
    private function request(string $url, ?array $get, ?array $post, array $headers): string {
        $factory = new HttpFactory();

        if (!empty($get)) {
            $query = http_build_query(array_filter($get, static fn ($v) => $v !== null));
            $url .= (str_contains($url, '?') ? '&' : '?') . $query;
        }

        $method  = !empty($post) ? 'POST' : 'GET';
        $request = $factory->createRequest($method, $url);
        foreach ($headers as $header) {
            [$name, $value] = array_map('trim', explode(':', $header, 2));
            $request        = $request->withHeader($name, $value);
        }
        if (!empty($post)) {
            $body    = http_build_query(array_filter($post, static fn ($v) => $v !== null));
            $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded')
                    ->withBody($factory->createStream($body));
        }

        $client = new CurlClient($factory, $factory);
        return (string) $client->sendRequest($request)->getBody();
    }

}
