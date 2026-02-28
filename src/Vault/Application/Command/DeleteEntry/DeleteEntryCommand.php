<?php

declare(strict_types=1);

namespace App\Vault\Application\Command\DeleteEntry;

final readonly class DeleteEntryCommand
{
    public function __construct(
        public string $entryId,
        public string $ownerId,
    ) {}
}
