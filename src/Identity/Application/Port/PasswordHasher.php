<?php

declare(strict_types=1);

namespace App\Identity\Application\Port;

use App\Identity\Domain\Model\HashedPassword;

interface PasswordHasher
{
    public function hash(string $plainPassword): HashedPassword;
}
