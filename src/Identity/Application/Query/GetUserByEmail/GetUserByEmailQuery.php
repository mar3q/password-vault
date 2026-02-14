<?php

declare(strict_types=1);

namespace App\Identity\Application\Query\GetUserByEmail;

final readonly class GetUserByEmailQuery
{
    public function __construct(
        public string $email,
    ) {}
}
