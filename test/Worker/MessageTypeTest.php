<?php

declare(strict_types=1);

namespace Rad\Test\Worker;

use PHPUnit\Framework\TestCase;
use Rad\Worker\MessageType;

final class MessageTypeTest extends TestCase {

    public function testBackedValues(): void {
        $this->assertSame(1, MessageType::DEFAULT->value);
        $this->assertSame(MessageType::COMMAND, MessageType::from(2));
    }

    public function testAllValuesArePositive(): void {
        foreach (MessageType::cases() as $case) {
            $this->assertGreaterThan(0, $case->value);
        }
    }
}
