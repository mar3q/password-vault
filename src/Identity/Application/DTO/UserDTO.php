<?php

declare(strict_types=1);

namespace App\Identity\Application\DTO;

use App\Identity\Domain\Model\User;

final readonly class UserDTO
{
    public function __construct(
        public string $id,
        public string $email,
        public string $username,
    ) {}

    public static function fromUser(User $user): self
    {
        return new self(
            $user->id()->value(),
            $user->email()->value(),
            $user->username()->value(),
        );
    }
}
