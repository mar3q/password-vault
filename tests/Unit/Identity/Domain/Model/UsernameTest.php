<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Domain\Model;

use App\Identity\Domain\Exception\InvalidUsernameException;
use App\Identity\Domain\Model\Username;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UsernameTest extends TestCase
{
    #[Test]
    public function it_creates_from_valid_username(): void
    {
        $username = new Username('john_doe');

        self::assertSame('john_doe', $username->value());
    }

    #[Test]
    public function it_allows_alphanumeric_and_dashes(): void
    {
        $username = new Username('user-123_test');

        self::assertSame('user-123_test', $username->value());
    }

    #[Test]
    public function it_trims_whitespace(): void
    {
        $username = new Username('  john  ');

        self::assertSame('john', $username->value());
    }

    #[Test]
    #[DataProvider('tooShortUsernames')]
    public function it_rejects_too_short_username(string $short): void
    {
        $this->expectException(InvalidUsernameException::class);

        new Username($short);
    }

    /** @return iterable<string, array{string}> */
    public static function tooShortUsernames(): iterable
    {
        yield 'empty' => [''];
        yield 'one char' => ['a'];
        yield 'two chars' => ['ab'];
    }

    #[Test]
    public function it_rejects_too_long_username(): void
    {
        $this->expectException(InvalidUsernameException::class);

        new Username(str_repeat('a', 65));
    }

    #[Test]
    public function it_rejects_invalid_characters(): void
    {
        $this->expectException(InvalidUsernameException::class);

        new Username('user@name');
    }

    #[Test]
    public function it_compares_equal_usernames(): void
    {
        $u1 = new Username('john');
        $u2 = new Username('john');

        self::assertTrue($u1->equals($u2));
    }

    #[Test]
    public function it_compares_different_usernames(): void
    {
        $u1 = new Username('john');
        $u2 = new Username('jane');

        self::assertFalse($u1->equals($u2));
    }
}
