<?php

declare(strict_types=1);

namespace App\Tests\Application\Vault\Command;

use App\Identity\Domain\Port\EventDispatcher;
use App\Vault\Application\Command\DeleteEntry\DeleteEntryCommand;
use App\Vault\Application\Command\DeleteEntry\DeleteEntryHandler;
use App\Vault\Domain\Exception\AccessDeniedException;
use App\Vault\Domain\Exception\EntryNotFoundException;
use App\Vault\Domain\Model\EntryId;
use App\Vault\Domain\Model\EntryTitle;
use App\Vault\Domain\Model\OwnerId;
use App\Vault\Domain\Model\VaultEntry;
use App\Vault\Domain\Port\VaultRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class DeleteEntryHandlerTest extends TestCase
{
    #[Test]
    public function it_deletes_entry(): void
    {
        $entryId = EntryId::generate();
        $ownerId = new OwnerId(Uuid::v4()->toRfc4122());

        $entry = VaultEntry::create($entryId, $ownerId, new EntryTitle('GitHub'), 'encrypted', null, null, null);
        $entry->releaseEvents();

        $repository = $this->createMock(VaultRepository::class);
        $repository->method('findById')->willReturn($entry);
        $repository->expects(self::once())->method('delete');

        $dispatcher = $this->createMock(EventDispatcher::class);
        $dispatcher->expects(self::once())->method('dispatch');

        $handler = new DeleteEntryHandler($repository, $dispatcher);

        $handler(new DeleteEntryCommand($entryId->value(), $ownerId->value()));
    }

    #[Test]
    public function it_throws_when_entry_not_found(): void
    {
        $repository = $this->createStub(VaultRepository::class);
        $repository->method('findById')->willReturn(null);

        $dispatcher = $this->createStub(EventDispatcher::class);

        $handler = new DeleteEntryHandler($repository, $dispatcher);

        $this->expectException(EntryNotFoundException::class);

        $handler(new DeleteEntryCommand(
            EntryId::generate()->value(),
            Uuid::v4()->toRfc4122(),
        ));
    }

    #[Test]
    public function it_throws_when_owner_does_not_match(): void
    {
        $entryId = EntryId::generate();
        $ownerId = new OwnerId(Uuid::v4()->toRfc4122());
        $differentOwnerId = Uuid::v4()->toRfc4122();

        $entry = VaultEntry::create($entryId, $ownerId, new EntryTitle('GitHub'), 'encrypted', null, null, null);
        $entry->releaseEvents();

        $repository = $this->createStub(VaultRepository::class);
        $repository->method('findById')->willReturn($entry);

        $dispatcher = $this->createStub(EventDispatcher::class);

        $handler = new DeleteEntryHandler($repository, $dispatcher);

        $this->expectException(AccessDeniedException::class);

        $handler(new DeleteEntryCommand($entryId->value(), $differentOwnerId));
    }
}
