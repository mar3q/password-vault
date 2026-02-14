<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception;

use App\Identity\Domain\Model\Email;

final class EmailAlreadyTakenException extends \DomainException
{
    public static function fromEmail(Email $email): self
    {
        return new self(sprintf('Email "%s" is already taken.', $email->value()));
    }
}
