<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Domain\Model;

use App\Identity\Domain\Model\HashedPassword;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HashedPasswordTest extends TestCase
{
    #[Test]
    public function it_creates_from_valid_hash(): void
    {
        $password = new HashedPassword('$2y$13$abc123');

        self::assertSame('$2y$13$abc123', $password->value());
    }

    #[Test]
    public function it_rejects_empty_hash(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Password hash cannot be empty.');

        new HashedPassword('');
    }

    #[Test]
    public function it_converts_to_string(): void
    {
        $hash = '$2y$13$abc123';
        $password = new HashedPassword($hash);

        self::assertSame($hash, (string) $password);
    }
}
