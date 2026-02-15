<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Infrastructure\Persistence\Type;

use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\HashedPassword;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Model\Username;
use App\Identity\Infrastructure\Persistence\Type\EmailType;
use App\Identity\Infrastructure\Persistence\Type\HashedPasswordType;
use App\Identity\Infrastructure\Persistence\Type\UserIdType;
use App\Identity\Infrastructure\Persistence\Type\UsernameType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DoctrineTypesTest extends TestCase
{
    private AbstractPlatform $platform;

    protected function setUp(): void
    {
        $this->platform = $this->createStub(AbstractPlatform::class);
    }

    // --- UserIdType ---

    #[Test]
    public function user_id_type_converts_to_php_value(): void
    {
        $type = new UserIdType();

        $result = $type->convertToPHPValue('550e8400-e29b-41d4-a716-446655440000', $this->platform);

        self::assertInstanceOf(UserId::class, $result);
        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $result->value());
    }

    #[Test]
    public function user_id_type_returns_null_for_null_php_value(): void
    {
        $type = new UserIdType();

        self::assertNull($type->convertToPHPValue(null, $this->platform));
    }

    #[Test]
    public function user_id_type_converts_vo_to_database_value(): void
    {
        $type = new UserIdType();
        $id = new UserId('550e8400-e29b-41d4-a716-446655440000');

        $result = $type->convertToDatabaseValue($id, $this->platform);

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $result);
    }

    #[Test]
    public function user_id_type_converts_string_to_database_value(): void
    {
        $type = new UserIdType();

        $result = $type->convertToDatabaseValue('550e8400-e29b-41d4-a716-446655440000', $this->platform);

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $result);
    }

    #[Test]
    public function user_id_type_returns_null_for_null_database_value(): void
    {
        $type = new UserIdType();

        self::assertNull($type->convertToDatabaseValue(null, $this->platform));
    }

    // --- EmailType ---

    #[Test]
    public function email_type_converts_to_php_value(): void
    {
        $type = new EmailType();

        $result = $type->convertToPHPValue('user@example.com', $this->platform);

        self::assertInstanceOf(Email::class, $result);
        self::assertSame('user@example.com', $result->value());
    }

    #[Test]
    public function email_type_returns_null_for_null_php_value(): void
    {
        $type = new EmailType();

        self::assertNull($type->convertToPHPValue(null, $this->platform));
    }

    #[Test]
    public function email_type_converts_vo_to_database_value(): void
    {
        $type = new EmailType();
        $email = new Email('user@example.com');

        self::assertSame('user@example.com', $type->convertToDatabaseValue($email, $this->platform));
    }

    #[Test]
    public function email_type_returns_null_for_null_database_value(): void
    {
        $type = new EmailType();

        self::assertNull($type->convertToDatabaseValue(null, $this->platform));
    }

    // --- UsernameType ---

    #[Test]
    public function username_type_converts_to_php_value(): void
    {
        $type = new UsernameType();

        $result = $type->convertToPHPValue('john_doe', $this->platform);

        self::assertInstanceOf(Username::class, $result);
        self::assertSame('john_doe', $result->value());
    }

    #[Test]
    public function username_type_returns_null_for_null_php_value(): void
    {
        $type = new UsernameType();

        self::assertNull($type->convertToPHPValue(null, $this->platform));
    }

    #[Test]
    public function username_type_converts_vo_to_database_value(): void
    {
        $type = new UsernameType();
        $username = new Username('john_doe');

        self::assertSame('john_doe', $type->convertToDatabaseValue($username, $this->platform));
    }

    #[Test]
    public function username_type_returns_null_for_null_database_value(): void
    {
        $type = new UsernameType();

        self::assertNull($type->convertToDatabaseValue(null, $this->platform));
    }

    // --- HashedPasswordType ---

    #[Test]
    public function hashed_password_type_converts_to_php_value(): void
    {
        $type = new HashedPasswordType();

        $result = $type->convertToPHPValue('$2y$13$abc', $this->platform);

        self::assertInstanceOf(HashedPassword::class, $result);
        self::assertSame('$2y$13$abc', $result->value());
    }

    #[Test]
    public function hashed_password_type_returns_null_for_null_php_value(): void
    {
        $type = new HashedPasswordType();

        self::assertNull($type->convertToPHPValue(null, $this->platform));
    }

    #[Test]
    public function hashed_password_type_converts_vo_to_database_value(): void
    {
        $type = new HashedPasswordType();
        $password = new HashedPassword('$2y$13$abc');

        self::assertSame('$2y$13$abc', $type->convertToDatabaseValue($password, $this->platform));
    }

    #[Test]
    public function hashed_password_type_returns_null_for_null_database_value(): void
    {
        $type = new HashedPasswordType();

        self::assertNull($type->convertToDatabaseValue(null, $this->platform));
    }
}
