<?php

declare(strict_types=1);

namespace App\Vault\Application\Query\ListEntries;

use App\Vault\Application\DTO\EntryDTO;
use App\Vault\Domain\Model\OwnerId;
use App\Vault\Domain\Port\Encrypter;
use App\Vault\Domain\Port\VaultRepository;

final readonly class ListEntriesHandler
{
    public function __construct(
        private VaultRepository $vaultRepository,
        private Encrypter $encrypter,
    ) {}

    /**
     * @return EntryDTO[]
     */
    public function __invoke(ListEntriesQuery $query): array
    {
        $entries = $this->vaultRepository->findByOwnerId(new OwnerId($query->ownerId));

        return array_map(
            fn($entry) => EntryDTO::fromEntry($entry, $this->encrypter->decrypt($entry->encryptedPassword())),
            $entries,
        );
    }
}
