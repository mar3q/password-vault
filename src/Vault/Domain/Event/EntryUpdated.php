<?php

declare(strict_types=1);

namespace App\Vault\Domain\Event;

use App\Vault\Domain\Model\EntryId;
use App\Vault\Domain\Model\OwnerId;

final readonly class EntryUpdated implements VaultDomainEvent
{
    private \DateTimeImmutable $occurredAt;

    public function __construct(
        private EntryId $entryId,
        private OwnerId $ownerId,
    ) {
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function entryId(): EntryId
    {
        return $this->entryId;
    }

    public function ownerId(): OwnerId
    {
        return $this->ownerId;
    }

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
