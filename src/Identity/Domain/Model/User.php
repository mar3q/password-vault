<?php

declare(strict_types=1);

namespace App\Identity\Domain\Model;

use App\Identity\Domain\Event\DomainEvent;
use App\Identity\Domain\Event\UserEmailChanged;
use App\Identity\Domain\Event\UserPasswordChanged;
use App\Identity\Domain\Event\UserRegistered;

class User
{
    /** @var DomainEvent[] */
    private array $domainEvents = [];

    private function __construct(
        private UserId $id,
        private Email $email,
        private Username $username,
        private HashedPassword $password,
    ) {}

    public static function register(
        UserId $id,
        Email $email,
        Username $username,
        HashedPassword $password,
    ): self {
        $user = new self($id, $email, $username, $password);
        $user->recordEvent(new UserRegistered($id, $email, $username));

        return $user;
    }

    public function changeEmail(Email $newEmail): void
    {
        if ($this->email->equals($newEmail)) {
            return;
        }

        $oldEmail = $this->email;
        $this->email = $newEmail;
        $this->recordEvent(new UserEmailChanged($this->id, $oldEmail, $newEmail));
    }

    public function changePassword(HashedPassword $newPassword): void
    {
        $this->password = $newPassword;
        $this->recordEvent(new UserPasswordChanged($this->id));
    }

    public function id(): UserId
    {
        return $this->id;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function username(): Username
    {
        return $this->username;
    }

    public function password(): HashedPassword
    {
        return $this->password;
    }

    /** @return DomainEvent[] */
    public function releaseEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    private function recordEvent(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }
}
