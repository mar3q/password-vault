<?php

declare(strict_types=1);

namespace App\Vault\Domain\Exception;

final class InvalidEntryTitleException extends \DomainException
{
    public static function fromLength(string $value, int $maxLength): self
    {
        if ($value === '') {
            return new self('Entry title cannot be empty.');
        }

        return new self(sprintf(
            'Entry title must be at most %d characters, got %d.',
            $maxLength,
            mb_strlen($value),
        ));
    }
}
