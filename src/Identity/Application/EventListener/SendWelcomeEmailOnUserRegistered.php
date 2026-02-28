<?php

declare(strict_types=1);

namespace App\Identity\Application\EventListener;

use App\Identity\Application\Port\EmailNotifier;
use App\Identity\Domain\Event\UserRegistered;

final readonly class SendWelcomeEmailOnUserRegistered
{
    public function __construct(
        private EmailNotifier $emailNotifier,
    ) {}

    public function __invoke(UserRegistered $event): void
    {
        $this->emailNotifier->sendWelcome($event->email(), $event->username()->value());
    }
}
