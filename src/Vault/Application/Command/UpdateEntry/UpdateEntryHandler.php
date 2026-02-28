<?php

declare(strict_types=1);

namespace App\Vault\Application\Command\UpdateEntry;

use App\Identity\Domain\Port\EventDispatcher;
use App\Vault\Domain\Exception\AccessDeniedException;
use App\Vault\Domain\Exception\EntryNotFoundException;
use App\Vault\Domain\Model\EntryId;
use App\Vault\Domain\Model\EntryTitle;
use App\Vault\Domain\Model\OwnerId;
use App\Vault\Domain\Port\Encrypter;
use App\Vault\Domain\Port\VaultRepository;

final readonly class UpdateEntryHandler
{
    public function __construct(
        private VaultRepository $vaultRepository,
        private Encrypter $encrypter,
        private EventDispatcher $eventDispatcher,
    ) {}

    public function __invoke(UpdateEntryCommand $command): void
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

        $entry->update(
            new EntryTitle($command->title),
            $this->encrypter->encrypt($command->plainPassword),
            $command->login,
            $command->url,
            $command->notes,
        );

        $this->vaultRepository->save($entry);
        $this->eventDispatcher->dispatch($entry->releaseEvents());
    }
}
