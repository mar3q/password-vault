<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Notification;

use App\Identity\Application\Port\EmailNotifier;
use App\Identity\Domain\Model\Email;
use Psr\Log\LoggerInterface;

final readonly class LoggingEmailNotifier implements EmailNotifier
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function sendWelcome(Email $email, string $username): void
    {
        $this->logger->info('Welcome email sent to {email} for user {username}', [
            'email' => $email->value(),
            'username' => $username,
        ]);
    }
}
