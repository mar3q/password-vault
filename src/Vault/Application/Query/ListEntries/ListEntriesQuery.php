<?php

declare(strict_types=1);

namespace App\Vault\Application\Query\ListEntries;

final readonly class ListEntriesQuery
{
    public function __construct(
        public string $ownerId,
    ) {}
}
