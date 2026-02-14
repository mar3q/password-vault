<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception;

use App\Identity\Domain\Model\UserId;

final class UserNotFoundException extends \DomainException
{
    public static function withId(UserId $id): self
    {
        return new self(sprintf('User with ID "%s" not found.', $id->value()));
    }

    public static function withEmail(string $email): self
    {
        return new self(sprintf('User with email "%s" not found.', $email));
    }
}
