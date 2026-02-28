<?php

declare(strict_types=1);

namespace App\Vault\Application\DTO;

use App\Vault\Domain\Model\VaultEntry;

final readonly class EntryDTO
{
    public function __construct(
        public string $id,
        public string $title,
        public ?string $login,
        public string $password,
        public ?string $url,
        public ?string $notes,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromEntry(VaultEntry $entry, string $decryptedPassword): self
    {
        return new self(
            $entry->id()->value(),
            $entry->title()->value(),
            $entry->login(),
            $decryptedPassword,
            $entry->url(),
            $entry->notes(),
            $entry->createdAt()->format(\DateTimeInterface::ATOM),
            $entry->updatedAt()->format(\DateTimeInterface::ATOM),
        );
    }
}
