<?php

declare(strict_types=1);

namespace App\Vault\Application\Query\GetEntryById;

use App\Vault\Application\DTO\EntryDTO;
use App\Vault\Domain\Exception\AccessDeniedException;
use App\Vault\Domain\Exception\EntryNotFoundException;
use App\Vault\Domain\Model\EntryId;
use App\Vault\Domain\Model\OwnerId;
use App\Vault\Domain\Port\Encrypter;
use App\Vault\Domain\Port\VaultRepository;

final readonly class GetEntryByIdHandler
{
    public function __construct(
        private VaultRepository $vaultRepository,
        private Encrypter $encrypter,
    ) {}

    public function __invoke(GetEntryByIdQuery $query): EntryDTO
    {
        $entryId = new EntryId($query->entryId);
        $ownerId = new OwnerId($query->ownerId);

        $entry = $this->vaultRepository->findById($entryId);

        if ($entry === null) {
            throw EntryNotFoundException::withId($entryId);
        }

        if (!$entry->ownerId()->equals($ownerId)) {
            throw AccessDeniedException::forEntry($entryId);
        }

        return EntryDTO::fromEntry($entry, $this->encrypter->decrypt($entry->encryptedPassword()));
    }
}
