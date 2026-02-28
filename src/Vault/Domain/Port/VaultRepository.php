<?php

declare(strict_types=1);

namespace App\Vault\Domain\Port;

use App\Vault\Domain\Model\EntryId;
use App\Vault\Domain\Model\OwnerId;
use App\Vault\Domain\Model\VaultEntry;

interface VaultRepository
{
    public function findById(EntryId $id): ?VaultEntry;

    /** @return VaultEntry[] */
    public function findByOwnerId(OwnerId $ownerId): array;

    public function save(VaultEntry $entry): void;

    public function delete(VaultEntry $entry): void;
}
