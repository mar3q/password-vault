<?php

declare(strict_types=1);

namespace App\Tests\Unit\Vault\Domain\Model;

use App\Vault\Domain\Model\EntryId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EntryIdTest extends TestCase
{
    #[Test]
    public function it_creates_from_valid_uuid(): void
    {
        $id = new EntryId('550e8400-e29b-41d4-a716-446655440000');

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $id->value());
    }

    #[Test]
    public function it_rejects_invalid_uuid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new EntryId('not-a-uuid');
    }

    #[Test]
    public function it_generates_unique_ids(): void
    {
        $id1 = EntryId::generate();
        $id2 = EntryId::generate();

        self::assertFalse($id1->equals($id2));
    }
}
