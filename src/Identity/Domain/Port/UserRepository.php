<?php

declare(strict_types=1);

namespace App\Identity\Domain\Port;

use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\User;
use App\Identity\Domain\Model\UserId;

interface UserRepository
{
    public function findById(UserId $id): ?User;

    public function findByEmail(Email $email): ?User;

    public function save(User $user): void;
}
