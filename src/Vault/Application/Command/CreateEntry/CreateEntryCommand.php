<?php

declare(strict_types=1);

namespace App\Vault\Application\Command\CreateEntry;

final readonly class CreateEntryCommand
{
    public function __construct(
        public string $ownerId,
        public string $title,
        public string $plainPassword,
        public ?string $login = null,
        public ?string $url = null,
        public ?string $notes = null,
    ) {}
}
