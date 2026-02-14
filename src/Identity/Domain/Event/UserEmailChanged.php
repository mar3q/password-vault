<?php

declare(strict_types=1);

namespace App\Identity\Domain\Event;

use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\UserId;

final readonly class UserEmailChanged implements DomainEvent
{
    private \DateTimeImmutable $occurredAt;

    public function __construct(
        private UserId $userId,
        private Email $oldEmail,
        private Email $newEmail,
    ) {
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function oldEmail(): Email
    {
        return $this->oldEmail;
    }

    public function newEmail(): Email
    {
        return $this->newEmail;
    }

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
