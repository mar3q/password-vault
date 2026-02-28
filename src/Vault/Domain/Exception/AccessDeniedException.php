<?php

declare(strict_types=1);

namespace App\Vault\Domain\Exception;

use App\Vault\Domain\Model\EntryId;

final class AccessDeniedException extends \DomainException
{
    public static function forEntry(EntryId $id): self
    {
        return new self(sprintf('Access denied for vault entry "%s".', $id->value()));
    }
}
