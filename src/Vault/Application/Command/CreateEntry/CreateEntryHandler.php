<?php

declare(strict_types=1);

namespace App\Vault\Application\Command\CreateEntry;

use App\Identity\Domain\Port\EventDispatcher;
use App\Vault\Application\DTO\EntryDTO;
use App\Vault\Domain\Model\EntryId;
use App\Vault\Domain\Model\EntryTitle;
use App\Vault\Domain\Model\OwnerId;
use App\Vault\Domain\Model\VaultEntry;
use App\Vault\Domain\Port\Encrypter;
use App\Vault\Domain\Port\VaultRepository;

final readonly class CreateEntryHandler
{
    public function __construct(
        private VaultRepository $vaultRepository,
        private Encrypter $encrypter,
        private EventDispatcher $eventDispatcher,
    ) {}

    public function __invoke(CreateEntryCommand $command): EntryDTO
    {
        $entry = VaultEntry::create(
            EntryId::generate(),
            new OwnerId($command->ownerId),
            new EntryTitle($command->title),
            $this->encrypter->encrypt($command->plainPassword),
            $command->login,
            $command->url,
            $command->notes,
        );

        $this->vaultRepository->save($entry);
        $this->eventDispatcher->dispatch($entry->releaseEvents());

        return EntryDTO::fromEntry($entry, $command->plainPassword);
    }
}
