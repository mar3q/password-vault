<?php

declare(strict_types=1);

namespace App\Vault\Domain\Model;

use App\Vault\Domain\Event\EntryCreated;
use App\Vault\Domain\Event\EntryDeleted;
use App\Vault\Domain\Event\EntryUpdated;
use App\Vault\Domain\Event\VaultDomainEvent;

class VaultEntry
{
    /** @var VaultDomainEvent[] */
    private array $domainEvents = [];

    private function __construct(
        private EntryId $id,
        private OwnerId $ownerId,
        private EntryTitle $title,
        private string $encryptedPassword,
        private ?string $login,
        private ?string $url,
        private ?string $notes,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        EntryId $id,
        OwnerId $ownerId,
        EntryTitle $title,
        string $encryptedPassword,
        ?string $login,
        ?string $url,
        ?string $notes,
    ): self {
        $now = new \DateTimeImmutable();
        $entry = new self($id, $ownerId, $title, $encryptedPassword, $login, $url, $notes, $now, $now);
        $entry->recordEvent(new EntryCreated($id, $ownerId));

        return $entry;
    }

    public function update(
        EntryTitle $title,
        string $encryptedPassword,
        ?string $login,
        ?string $url,
        ?string $notes,
    ): void {
        $this->title = $title;
        $this->encryptedPassword = $encryptedPassword;
        $this->login = $login;
        $this->url = $url;
        $this->notes = $notes;
        $this->updatedAt = new \DateTimeImmutable();
        $this->recordEvent(new EntryUpdated($this->id, $this->ownerId));
    }

    public function markDeleted(): void
    {
        $this->recordEvent(new EntryDeleted($this->id, $this->ownerId));
    }

    public function id(): EntryId
    {
        return $this->id;
    }

    public function ownerId(): OwnerId
    {
        return $this->ownerId;
    }

    public function title(): EntryTitle
    {
        return $this->title;
    }

    public function encryptedPassword(): string
    {
        return $this->encryptedPassword;
    }

    public function login(): ?string
    {
        return $this->login;
    }

    public function url(): ?string
    {
        return $this->url;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /** @return VaultDomainEvent[] */
    public function releaseEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    private function recordEvent(VaultDomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }
}
