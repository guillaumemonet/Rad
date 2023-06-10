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
use Rad\Http\HttpClient;

/**
 * Description of PHPSessionHandler
 *
 * @author guillaume
 */
class ClientApiHandler implements ClientApiInterface {

    public function call(string $endpoint, array $get = null, array $post = null, array $headers = []) {
        $cfg     = Config::getServiceConfig('clientapi', 'rad')->config;
        $url     = $cfg->url;
        $token   = $cfg->token;
        $cache   = boolval($cfg->cache_enabled);
        $fullUrl = $url . $endpoint;

        $c_key = "cache_clientapi_" . md5($fullUrl . $token);
        $datas = unserialize(Cache::getHandler()->get($c_key));
        if ($datas === false || !$cache) {
            $headers[] = 'Authorization: ' . $token;
            $datas     = HttpClient::doRequest($fullUrl, $get, $post, $headers);
            Cache::getHandler()->set($c_key, serialize($datas));
        }
        return $datas;
    }

}
