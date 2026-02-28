<?php

declare(strict_types=1);

namespace App\Vault\Domain\Model;

use App\Vault\Domain\Exception\InvalidEntryTitleException;

final readonly class EntryTitle
{
    private const int MAX_LENGTH = 255;

    private string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if ($trimmed === '' || mb_strlen($trimmed) > self::MAX_LENGTH) {
            throw InvalidEntryTitleException::fromLength($trimmed, self::MAX_LENGTH);
        }

        $this->value = $trimmed;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
