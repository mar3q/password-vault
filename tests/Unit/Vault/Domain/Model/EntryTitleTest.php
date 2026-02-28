<?php

declare(strict_types=1);

namespace App\Tests\Unit\Vault\Domain\Model;

use App\Vault\Domain\Exception\InvalidEntryTitleException;
use App\Vault\Domain\Model\EntryTitle;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EntryTitleTest extends TestCase
{
    #[Test]
    public function it_creates_valid_title(): void
    {
        $title = new EntryTitle('GitHub');

        self::assertSame('GitHub', $title->value());
    }

    #[Test]
    public function it_trims_whitespace(): void
    {
        $title = new EntryTitle('  GitHub  ');

        self::assertSame('GitHub', $title->value());
    }

    #[Test]
    public function it_rejects_empty_title(): void
    {
        $this->expectException(InvalidEntryTitleException::class);

        new EntryTitle('');
    }

    #[Test]
    public function it_rejects_title_exceeding_max_length(): void
    {
        $this->expectException(InvalidEntryTitleException::class);

        new EntryTitle(str_repeat('a', 256));
    }
}
