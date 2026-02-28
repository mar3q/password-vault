<?php

declare(strict_types=1);

namespace App\Vault\Infrastructure\Persistence\Type;

use App\Vault\Domain\Model\EntryId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class EntryIdType extends StringType
{
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?EntryId
    {
        if ($value === null) {
            return null;
        }

        return new EntryId((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof EntryId) {
            return $value->value();
        }

        return (string) $value;
    }
}
