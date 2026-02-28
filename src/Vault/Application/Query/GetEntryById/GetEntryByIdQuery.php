<?php

declare(strict_types=1);

namespace App\Vault\Application\Query\GetEntryById;

final readonly class GetEntryByIdQuery
{
    public function __construct(
        public string $entryId,
        public string $ownerId,
    ) {}
}
