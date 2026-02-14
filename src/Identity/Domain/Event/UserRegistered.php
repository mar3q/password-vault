<?php

declare(strict_types=1);

namespace App\Identity\Domain\Event;

use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Model\Username;

final readonly class UserRegistered implements DomainEvent
{
    private \DateTimeImmutable $occurredAt;

    public function __construct(
        private UserId $userId,
        private Email $email,
        private Username $username,
    ) {
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function username(): Username
    {
        return $this->username;
    }

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
