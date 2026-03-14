<?php

declare(strict_types=1);

namespace App\Tests\Application\Vault\Command;

use App\Identity\Domain\Port\EventDispatcher;
use App\Vault\Application\Command\UpdateEntry\UpdateEntryCommand;
use App\Vault\Application\Command\UpdateEntry\UpdateEntryHandler;
use App\Vault\Domain\Exception\AccessDeniedException;
use App\Vault\Domain\Exception\EntryNotFoundException;
use App\Vault\Domain\Model\EntryId;
use App\Vault\Domain\Model\EntryTitle;
use App\Vault\Domain\Model\OwnerId;
use App\Vault\Domain\Model\VaultEntry;
use App\Vault\Domain\Port\Encrypter;
use App\Vault\Domain\Port\VaultRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class UpdateEntryHandlerTest extends TestCase
{
    #[Test]
    public function it_updates_entry(): void
    {
        $entryId = EntryId::generate();
        $ownerId = new OwnerId(Uuid::v4()->toRfc4122());

        $entry = VaultEntry::create($entryId, $ownerId, new EntryTitle('Old Title'), 'encrypted', null, null, null);
        $entry->releaseEvents();

        $repository = $this->createMock(VaultRepository::class);
        $repository->method('findById')->willReturn($entry);
        $repository->expects(self::once())->method('save');

        $encrypter = $this->createStub(Encrypter::class);
        $encrypter->method('encrypt')->willReturn('new_encrypted');

        $dispatcher = $this->createMock(EventDispatcher::class);
        $dispatcher->expects(self::once())->method('dispatch');

        $handler = new UpdateEntryHandler($repository, $encrypter, $dispatcher);

        $handler(new UpdateEntryCommand(
            $entryId->value(),
            $ownerId->value(),
            'New Title',
            'new_password',
            'login',
            'https://example.com',
            'notes',
        ));

        self::assertSame('New Title', $entry->title()->value());
        self::assertSame('new_encrypted', $entry->encryptedPassword());
    }

    #[Test]
    public function it_throws_when_entry_not_found(): void
    {
        $repository = $this->createStub(VaultRepository::class);
        $repository->method('findById')->willReturn(null);

        $encrypter = $this->createStub(Encrypter::class);
        $dispatcher = $this->createStub(EventDispatcher::class);

        $handler = new UpdateEntryHandler($repository, $encrypter, $dispatcher);

        $this->expectException(EntryNotFoundException::class);

        $handler(new UpdateEntryCommand(
            EntryId::generate()->value(),
            Uuid::v4()->toRfc4122(),
            'Title',
            'password',
        ));
    }

    #[Test]
    public function it_throws_when_owner_does_not_match(): void
    {
        $entryId = EntryId::generate();
        $ownerId = new OwnerId(Uuid::v4()->toRfc4122());
        $differentOwnerId = Uuid::v4()->toRfc4122();

        $entry = VaultEntry::create($entryId, $ownerId, new EntryTitle('Title'), 'encrypted', null, null, null);
        $entry->releaseEvents();

        $repository = $this->createStub(VaultRepository::class);
        $repository->method('findById')->willReturn($entry);

        $encrypter = $this->createStub(Encrypter::class);
        $dispatcher = $this->createStub(EventDispatcher::class);

        $handler = new UpdateEntryHandler($repository, $encrypter, $dispatcher);

        $this->expectException(AccessDeniedException::class);

        $handler(new UpdateEntryCommand(
            $entryId->value(),
            $differentOwnerId,
            'Title',
            'password',
        ));
    }
}
