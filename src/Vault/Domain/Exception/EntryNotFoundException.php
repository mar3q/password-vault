<?php

declare(strict_types=1);

namespace App\Vault\Domain\Exception;

use App\Vault\Domain\Model\EntryId;

final class EntryNotFoundException extends \DomainException
{
    public static function withId(EntryId $id): self
    {
        return new self(sprintf('Vault entry with ID "%s" not found.', $id->value()));
    }
}
