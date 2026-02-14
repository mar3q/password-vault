<?php

declare(strict_types=1);

namespace App\Identity\Domain\Event;

use App\Identity\Domain\Model\UserId;

final readonly class UserPasswordChanged implements DomainEvent
{
    private \DateTimeImmutable $occurredAt;

    public function __construct(
        private UserId $userId,
    ) {
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
