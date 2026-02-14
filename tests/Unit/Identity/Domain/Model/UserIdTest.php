<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Domain\Model;

use App\Identity\Domain\Model\UserId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UserIdTest extends TestCase
{
    #[Test]
    public function it_creates_from_valid_uuid(): void
    {
        $id = new UserId('550e8400-e29b-41d4-a716-446655440000');

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $id->value());
    }

    #[Test]
    public function it_generates_a_valid_uuid(): void
    {
        $id = UserId::generate();

        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $id->value(),
        );
    }

    #[Test]
    public function it_rejects_invalid_uuid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new UserId('not-a-uuid');
    }

    #[Test]
    public function it_compares_equal_ids(): void
    {
        $id1 = new UserId('550e8400-e29b-41d4-a716-446655440000');
        $id2 = new UserId('550e8400-e29b-41d4-a716-446655440000');

        self::assertTrue($id1->equals($id2));
    }

    #[Test]
    public function it_compares_different_ids(): void
    {
        $id1 = UserId::generate();
        $id2 = UserId::generate();

        self::assertFalse($id1->equals($id2));
    }

    #[Test]
    public function it_converts_to_string(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $id = new UserId($uuid);

        self::assertSame($uuid, (string) $id);
    }
}
