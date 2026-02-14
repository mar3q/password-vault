<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\ChangeEmail;

final readonly class ChangeEmailCommand
{
    public function __construct(
        public string $userId,
        public string $newEmail,
    ) {}
}
