<?php

declare(strict_types=1);

namespace App\Tests\Unit\Vault\Domain\Model;

use App\Vault\Domain\Event\EntryCreated;
use App\Vault\Domain\Event\EntryDeleted;
use App\Vault\Domain\Event\EntryUpdated;
use App\Vault\Domain\Model\EntryId;
use App\Vault\Domain\Model\EntryTitle;
use App\Vault\Domain\Model\OwnerId;
use App\Vault\Domain\Model\VaultEntry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class VaultEntryTest extends TestCase
{
    #[Test]
    public function it_creates_entry_and_records_event(): void
    {
        $entry = VaultEntry::create(
            EntryId::generate(),
            new OwnerId('550e8400-e29b-41d4-a716-446655440000'),
            new EntryTitle('GitHub'),
            'encrypted-data',
            'john@example.com',
            'https://github.com',
            'Work account',
        );

        self::assertSame('GitHub', $entry->title()->value());
        self::assertSame('john@example.com', $entry->login());
        self::assertSame('https://github.com', $entry->url());
        self::assertSame('Work account', $entry->notes());

        $events = $entry->releaseEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(EntryCreated::class, $events[0]);
    }

    #[Test]
    public function it_updates_entry_and_records_event(): void
    {
        $entry = VaultEntry::create(
            EntryId::generate(),
            new OwnerId('550e8400-e29b-41d4-a716-446655440000'),
            new EntryTitle('GitHub'),
            'encrypted-data',
            null,
            null,
            null,
        );
        $entry->releaseEvents();

        $entry->update(
            new EntryTitle('GitLab'),
            'new-encrypted-data',
            'user@gitlab.com',
            'https://gitlab.com',
            null,
        );

        self::assertSame('GitLab', $entry->title()->value());
        self::assertSame('user@gitlab.com', $entry->login());

        $events = $entry->releaseEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(EntryUpdated::class, $events[0]);
    }

    #[Test]
    public function it_records_deleted_event(): void
    {
        $entry = VaultEntry::create(
            EntryId::generate(),
            new OwnerId('550e8400-e29b-41d4-a716-446655440000'),
            new EntryTitle('ToDelete'),
            'encrypted-data',
            null,
            null,
            null,
        );
        $entry->releaseEvents();

        $entry->markDeleted();

        $events = $entry->releaseEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(EntryDeleted::class, $events[0]);
    }

    #[Test]
    public function it_creates_entry_with_nullable_fields(): void
    {
        $entry = VaultEntry::create(
            EntryId::generate(),
            new OwnerId('550e8400-e29b-41d4-a716-446655440000'),
            new EntryTitle('Minimal'),
            'encrypted-data',
            null,
            null,
            null,
        );

        self::assertNull($entry->login());
        self::assertNull($entry->url());
        self::assertNull($entry->notes());
    }
}
