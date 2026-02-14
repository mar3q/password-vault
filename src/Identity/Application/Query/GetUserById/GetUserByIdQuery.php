<?php

declare(strict_types=1);

namespace App\Identity\Application\Query\GetUserById;

final readonly class GetUserByIdQuery
{
    public function __construct(
        public string $userId,
    ) {}
}
