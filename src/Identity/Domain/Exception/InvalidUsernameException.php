<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception;

final class InvalidUsernameException extends \DomainException
{
    public static function fromLength(string $username, int $min, int $max): self
    {
        return new self(sprintf(
            'Username "%s" must be between %d and %d characters.',
            $username,
            $min,
            $max,
        ));
    }

    public static function fromInvalidCharacters(string $username): self
    {
        return new self(sprintf(
            'Username "%s" contains invalid characters. Only alphanumeric, underscore and dash are allowed.',
            $username,
        ));
    }
}
