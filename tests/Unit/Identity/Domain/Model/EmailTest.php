<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Domain\Model;

use App\Identity\Domain\Exception\InvalidEmailException;
use App\Identity\Domain\Model\Email;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    #[Test]
    public function it_creates_from_valid_email(): void
    {
        $email = new Email('user@example.com');

        self::assertSame('user@example.com', $email->value());
    }

    #[Test]
    public function it_normalizes_to_lowercase(): void
    {
        $email = new Email('User@Example.COM');

        self::assertSame('user@example.com', $email->value());
    }

    #[Test]
    public function it_trims_whitespace(): void
    {
        $email = new Email('  user@example.com  ');

        self::assertSame('user@example.com', $email->value());
    }

    #[Test]
    #[DataProvider('invalidEmails')]
    public function it_rejects_invalid_email(string $invalid): void
    {
        $this->expectException(InvalidEmailException::class);

        new Email($invalid);
    }

    /** @return iterable<string, array{string}> */
    public static function invalidEmails(): iterable
    {
        yield 'empty string' => [''];
        yield 'no at sign' => ['invalid'];
        yield 'no domain' => ['user@'];
        yield 'no local part' => ['@example.com'];
        yield 'spaces only' => ['   '];
    }

    #[Test]
    public function it_compares_equal_emails(): void
    {
        $email1 = new Email('user@example.com');
        $email2 = new Email('USER@example.com');

        self::assertTrue($email1->equals($email2));
    }

    #[Test]
    public function it_compares_different_emails(): void
    {
        $email1 = new Email('user1@example.com');
        $email2 = new Email('user2@example.com');

        self::assertFalse($email1->equals($email2));
    }
}
