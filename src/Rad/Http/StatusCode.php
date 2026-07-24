<?php

declare(strict_types=1);

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Http;

/**
 * HTTP status codes as a backed enum.
 *
 * The historical `StatusCode::HTTP_*` integer constants are kept for backward
 * compatibility; new code can use the type-safe cases (StatusCode::OK, ...).
 */
enum StatusCode: int {

    // Informational 1xx
    case CONTINUE                        = 100;
    case SWITCHING_PROTOCOLS             = 101;
    // Successful 2xx
    case OK                              = 200;
    case CREATED                         = 201;
    case ACCEPTED                        = 202;
    case NONAUTHORITATIVE_INFORMATION    = 203;
    case NO_CONTENT                      = 204;
    case RESET_CONTENT                   = 205;
    case PARTIAL_CONTENT                 = 206;
    // Redirection 3xx
    case MULTIPLE_CHOICES                = 300;
    case MOVED_PERMANENTLY               = 301;
    case FOUND                           = 302;
    case SEE_OTHER                       = 303;
    case NOT_MODIFIED                    = 304;
    case USE_PROXY                       = 305;
    case UNUSED                          = 306;
    case TEMPORARY_REDIRECT              = 307;
    // Client Error 4xx
    case BAD_REQUEST                     = 400;
    case UNAUTHORIZED                    = 401;
    case PAYMENT_REQUIRED                = 402;
    case FORBIDDEN                       = 403;
    case NOT_FOUND                       = 404;
    case METHOD_NOT_ALLOWED              = 405;
    case NOT_ACCEPTABLE                  = 406;
    case PROXY_AUTHENTICATION_REQUIRED   = 407;
    case REQUEST_TIMEOUT                 = 408;
    case CONFLICT                        = 409;
    case GONE                            = 410;
    case LENGTH_REQUIRED                 = 411;
    case PRECONDITION_FAILED             = 412;
    case REQUEST_ENTITY_TOO_LARGE        = 413;
    case REQUEST_URI_TOO_LONG            = 414;
    case UNSUPPORTED_MEDIA_TYPE          = 415;
    case REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    case EXPECTATION_FAILED              = 417;
    case I_M_A_TEAPOT                    = 418;
    // Server Error 5xx
    case INTERNAL_SERVER_ERROR           = 500;
    case NOT_IMPLEMENTED                 = 501;
    case BAD_GATEWAY                     = 502;
    case SERVICE_UNAVAILABLE             = 503;
    case GATEWAY_TIMEOUT                 = 504;
    case VERSION_NOT_SUPPORTED           = 505;

    // ---- Backward-compatible integer constants ------------------------------
    const HTTP_CONTINUE                        = 100;
    const HTTP_SWITCHING_PROTOCOLS             = 101;
    const HTTP_OK                              = 200;
    const HTTP_CREATED                         = 201;
    const HTTP_ACCEPTED                        = 202;
    const HTTP_NONAUTHORITATIVE_INFORMATION    = 203;
    const HTTP_NO_CONTENT                      = 204;
    const HTTP_RESET_CONTENT                   = 205;
    const HTTP_PARTIAL_CONTENT                 = 206;
    const HTTP_MULTIPLE_CHOICES                = 300;
    const HTTP_MOVED_PERMANENTLY               = 301;
    const HTTP_FOUND                           = 302;
    const HTTP_SEE_OTHER                       = 303;
    const HTTP_NOT_MODIFIED                    = 304;
    const HTTP_USE_PROXY                       = 305;
    const HTTP_UNUSED                          = 306;
    const HTTP_TEMPORARY_REDIRECT              = 307;
    const HTTP_BAD_REQUEST                     = 400;
    const HTTP_UNAUTHORIZED                    = 401;
    const HTTP_PAYMENT_REQUIRED                = 402;
    const HTTP_FORBIDDEN                       = 403;
    const HTTP_NOT_FOUND                       = 404;
    const HTTP_METHOD_NOT_ALLOWED              = 405;
    const HTTP_NOT_ACCEPTABLE                  = 406;
    const HTTP_PROXY_AUTHENTICATION_REQUIRED   = 407;
    const HTTP_REQUEST_TIMEOUT                 = 408;
    const HTTP_CONFLICT                        = 409;
    const HTTP_GONE                            = 410;
    const HTTP_LENGTH_REQUIRED                 = 411;
    const HTTP_PRECONDITION_FAILED             = 412;
    const HTTP_REQUEST_ENTITY_TOO_LARGE        = 413;
    const HTTP_REQUEST_URI_TOO_LONG            = 414;
    const HTTP_UNSUPPORTED_MEDIA_TYPE          = 415;
    const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const HTTP_EXPECTATION_FAILED              = 417;
    const HTTP_I_M_A_TEAPOT                    = 418;
    const HTTP_INTERNAL_SERVER_ERROR           = 500;
    const HTTP_NOT_IMPLEMENTED                 = 501;
    const HTTP_BAD_GATEWAY                     = 502;
    const HTTP_SERVICE_UNAVAILABLE             = 503;
    const HTTP_GATEWAY_TIMEOUT                 = 504;
    const HTTP_VERSION_NOT_SUPPORTED           = 505;

    /**
     * Human-readable reason phrase for this status.
     */
    public function message(): string {
        return self::messages()[$this->value] ?? (string) $this->value;
    }

    /**
     * @return array<int, string>
     */
    private static function messages(): array {
        return [
            100 => '100 Continue',
            101 => '101 Switching Protocols',
            200 => '200 OK',
            201 => '201 Created',
            202 => '202 Accepted',
            203 => '203 Non-Authoritative Information',
            204 => '204 No Content',
            205 => '205 Reset Content',
            206 => '206 Partial Content',
            300 => '300 Multiple Choices',
            301 => '301 Moved Permanently',
            302 => '302 Found',
            303 => '303 See Other',
            304 => '304 Not Modified',
            305 => '305 Use Proxy',
            306 => '306 (Unused)',
            307 => '307 Temporary Redirect',
            400 => '400 Bad Request',
            401 => '401 Unauthorized',
            402 => '402 Payment Required',
            403 => '403 Forbidden',
            404 => '404 Not Found',
            405 => '405 Method Not Allowed',
            406 => '406 Not Acceptable',
            407 => '407 Proxy Authentication Required',
            408 => '408 Request Timeout',
            409 => '409 Conflict',
            410 => '410 Gone',
            411 => '411 Length Required',
            412 => '412 Precondition Failed',
            413 => '413 Request Entity Too Large',
            414 => '414 Request-URI Too Long',
            415 => '415 Unsupported Media Type',
            416 => '416 Requested Range Not Satisfiable',
            417 => '417 Expectation Failed',
            418 => 'I\'m a teapot',
            500 => '500 Internal Server Error',
            501 => '501 Not Implemented',
            502 => '502 Bad Gateway',
            503 => '503 Service Unavailable',
            504 => '504 Gateway Timeout',
            505 => '505 HTTP Version Not Supported',
        ];
    }

    public static function httpHeaderFor(int $code): ?string {
        $messages = self::messages();
        return isset($messages[$code]) ? 'HTTP/2.0 ' . $messages[$code] : null;
    }

    public static function getMessageForCode(int $code): ?string {
        return self::messages()[$code] ?? null;
    }

    public static function isError(int $code): bool {
        return $code >= self::HTTP_BAD_REQUEST;
    }

    public static function isRedirect(int $code): bool {
        return $code >= self::HTTP_MULTIPLE_CHOICES && $code < self::HTTP_BAD_REQUEST;
    }

    public static function canHaveBody(int $code): bool {
        return ($code < self::HTTP_CONTINUE || $code >= self::HTTP_OK) && $code != self::HTTP_NO_CONTENT && $code != self::HTTP_NOT_MODIFIED;
    }
}
