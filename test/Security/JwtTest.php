<?php

declare(strict_types=1);

namespace Rad\Test\Security;

use PHPUnit\Framework\TestCase;
use Rad\Security\Jwt;
use Rad\Security\JwtException;

final class JwtTest extends TestCase {
    private const SECRET = 'test-secret';

    public function testRoundTrip(): void {
        $token   = Jwt::encode(['sub' => 42, 'role' => 'admin'], self::SECRET);
        $payload = Jwt::decode($token, self::SECRET);

        $this->assertSame(42, $payload['sub']);
        $this->assertSame('admin', $payload['role']);
        $this->assertArrayHasKey('iat', $payload);
        $this->assertArrayHasKey('exp', $payload);
    }

    public function testTokenHasThreeSegments(): void {
        $token = Jwt::encode(['sub' => 1], self::SECRET);
        $this->assertCount(3, explode('.', $token));
    }

    public function testWrongSecretFailsSignature(): void {
        $token = Jwt::encode(['sub' => 1], self::SECRET);
        $this->expectException(JwtException::class);
        Jwt::decode($token, 'other-secret');
    }

    public function testAlgorithmConfusionRejected(): void {
        $token = Jwt::encode(['sub' => 1], self::SECRET, 3600, 'HS256');
        $this->expectException(JwtException::class);
        Jwt::decode($token, self::SECRET, 'HS512');
    }

    public function testExpiredTokenRejected(): void {
        $token = Jwt::encode(['sub' => 1, 'exp' => time() - 10], self::SECRET, 0);
        $this->expectException(JwtException::class);
        Jwt::decode($token, self::SECRET);
    }

    public function testNotYetValidTokenRejected(): void {
        $token = Jwt::encode(['sub' => 1, 'nbf' => time() + 60], self::SECRET);
        $this->expectException(JwtException::class);
        Jwt::decode($token, self::SECRET);
    }

    public function testMalformedTokenRejected(): void {
        $this->expectException(JwtException::class);
        Jwt::decode('not-a-jwt', self::SECRET);
    }
}
