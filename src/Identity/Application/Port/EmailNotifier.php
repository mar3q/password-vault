<?php

declare(strict_types=1);

namespace App\Identity\Application\Port;

use App\Identity\Domain\Model\Email;

interface EmailNotifier
{
    public function sendWelcome(Email $email, string $username): void;
}
