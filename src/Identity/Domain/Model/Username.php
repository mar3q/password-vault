<?php

declare(strict_types=1);

namespace App\Identity\Domain\Model;

use App\Identity\Domain\Exception\InvalidUsernameException;

final readonly class Username
{
    private const int MIN_LENGTH = 3;
    private const int MAX_LENGTH = 64;

    private string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if (mb_strlen($trimmed) < self::MIN_LENGTH || mb_strlen($trimmed) > self::MAX_LENGTH) {
            throw InvalidUsernameException::fromLength($trimmed, self::MIN_LENGTH, self::MAX_LENGTH);
        }

        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $trimmed)) {
            throw InvalidUsernameException::fromInvalidCharacters($trimmed);
        }

        $this->value = $trimmed;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
