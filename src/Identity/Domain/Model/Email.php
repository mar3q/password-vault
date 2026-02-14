<?php

declare(strict_types=1);

namespace App\Identity\Domain\Model;

use App\Identity\Domain\Exception\InvalidEmailException;

final readonly class Email
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = mb_strtolower(trim($value));

        if ($normalized === '' || !filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            throw InvalidEmailException::fromString($value);
        }

        $this->value = $normalized;
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
