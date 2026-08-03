<?php

declare(strict_types=1);

namespace Rad\Test\Http;

use PHPUnit\Framework\TestCase;
use Rad\Http\StatusCode;

final class StatusCodeTest extends TestCase {
    public function testBackedCases(): void {
        $this->assertSame(200, StatusCode::OK->value);
        $this->assertSame(StatusCode::OK, StatusCode::from(200));
        $this->assertNull(StatusCode::tryFrom(999));
    }

    public function testMessage(): void {
        $this->assertSame('200 OK', StatusCode::OK->message());
        $this->assertSame('404 Not Found', StatusCode::NOT_FOUND->message());
    }

    public function testStaticMessageHelpers(): void {
        $this->assertSame('404 Not Found', StatusCode::getMessageForCode(404));
        $this->assertNull(StatusCode::getMessageForCode(999));
        $this->assertSame('HTTP/2.0 200 OK', StatusCode::httpHeaderFor(200));
        $this->assertNull(StatusCode::httpHeaderFor(999));
    }

    public function testClassifiers(): void {
        $this->assertTrue(StatusCode::isError(404));
        $this->assertFalse(StatusCode::isError(200));
        $this->assertTrue(StatusCode::isRedirect(301));
        $this->assertFalse(StatusCode::isRedirect(200));
        $this->assertFalse(StatusCode::canHaveBody(204));
        $this->assertTrue(StatusCode::canHaveBody(200));
    }

    public function testBackwardCompatibleConstants(): void {
        $this->assertSame(200, StatusCode::HTTP_OK);
        $this->assertSame(404, StatusCode::HTTP_NOT_FOUND);
        $this->assertSame(500, StatusCode::HTTP_INTERNAL_SERVER_ERROR);
    }
}
