<?php

declare(strict_types=1);

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Security;

/**
 * Minimal, dependency-free JSON Web Token codec (HMAC signatures).
 *
 * Only HMAC algorithms are supported (HS256/HS384/HS512); the expected
 * algorithm is always enforced on decode to prevent algorithm-confusion
 * attacks. Registered claims iat/exp are added on encode and validated
 * (with nbf/exp) on decode.
 */
final class Jwt {
    private const ALGOS = [
        'HS256' => 'sha256',
        'HS384' => 'sha384',
        'HS512' => 'sha512',
    ];

    /**
     * @param array<string, mixed> $payload
     */
    public static function encode(array $payload, string $secret, int $ttl = 3600, string $alg = 'HS256'): string {
        if (!isset(self::ALGOS[$alg])) {
            throw new JwtException('Unsupported algorithm: ' . $alg);
        }
        $now = time();
        $payload += ['iat' => $now];
        if ($ttl > 0 && !isset($payload['exp'])) {
            $payload['exp'] = $now + $ttl;
        }

        $segments = [
            self::b64encode((string) json_encode(['typ' => 'JWT', 'alg' => $alg])),
            self::b64encode((string) json_encode($payload)),
        ];
        $signing    = implode('.', $segments);
        $segments[] = self::b64encode(self::sign($signing, $secret, $alg));
        return implode('.', $segments);
    }

    /**
     * @return array<string, mixed>
     * @throws JwtException
     */
    public static function decode(string $token, string $secret, string $alg = 'HS256', int $leeway = 0): array {
        if (!isset(self::ALGOS[$alg])) {
            throw new JwtException('Unsupported algorithm: ' . $alg);
        }
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new JwtException('Malformed token');
        }
        [$headB64, $payloadB64, $sigB64] = $parts;

        $header = json_decode(self::b64decode($headB64), true);
        if (!is_array($header) || ($header['alg'] ?? null) !== $alg) {
            throw new JwtException('Unexpected token algorithm');
        }

        $expected = self::sign($headB64 . '.' . $payloadB64, $secret, $alg);
        if (!hash_equals($expected, self::b64decode($sigB64))) {
            throw new JwtException('Signature verification failed');
        }

        $payload = json_decode(self::b64decode($payloadB64), true);
        if (!is_array($payload)) {
            throw new JwtException('Invalid token payload');
        }

        $now = time();
        if (isset($payload['nbf']) && $now + $leeway < (int) $payload['nbf']) {
            throw new JwtException('Token not yet valid');
        }
        if (isset($payload['exp']) && $now - $leeway >= (int) $payload['exp']) {
            throw new JwtException('Token expired');
        }
        return $payload;
    }

    private static function sign(string $data, string $secret, string $alg): string {
        return hash_hmac(self::ALGOS[$alg], $data, $secret, true);
    }

    private static function b64encode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function b64decode(string $data): string {
        $decoded = base64_decode(strtr($data, '-_', '+/'), true);
        if ($decoded === false) {
            throw new JwtException('Invalid base64url segment');
        }
        return $decoded;
    }
}
