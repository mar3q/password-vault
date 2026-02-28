<?php

declare(strict_types=1);

namespace App\Vault\Application\Command\DeleteEntry;

use App\Identity\Domain\Port\EventDispatcher;
use App\Vault\Domain\Exception\AccessDeniedException;
use App\Vault\Domain\Exception\EntryNotFoundException;
use App\Vault\Domain\Model\EntryId;
use App\Vault\Domain\Model\OwnerId;
use App\Vault\Domain\Port\VaultRepository;

final readonly class DeleteEntryHandler
{
    public function __construct(
        private VaultRepository $vaultRepository,
        private EventDispatcher $eventDispatcher,
    ) {}

    public function __invoke(DeleteEntryCommand $command): void
    {
        $entryId = new EntryId($command->entryId);
        $ownerId = new OwnerId($command->ownerId);

        $entry = $this->vaultRepository->findById($entryId);

        if ($entry === null) {
            throw EntryNotFoundException::withId($entryId);
        }

        if (!$entry->ownerId()->equals($ownerId)) {
            throw AccessDeniedException::forEntry($entryId);
        }

        $entry->markDeleted();
        $this->eventDispatcher->dispatch($entry->releaseEvents());
        $this->vaultRepository->delete($entry);
    }
}
