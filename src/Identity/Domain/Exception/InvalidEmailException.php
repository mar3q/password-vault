<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception;

final class InvalidEmailException extends \DomainException
{
    public static function fromString(string $email): self
    {
        return new self(sprintf('Invalid email address: "%s".', $email));
    }
}
