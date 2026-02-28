<?php

declare(strict_types=1);

namespace App\Vault\Infrastructure\Persistence\Type;

use App\Vault\Domain\Model\EntryTitle;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class EntryTitleType extends StringType
{
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?EntryTitle
    {
        if ($value === null) {
            return null;
        }

        return new EntryTitle((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof EntryTitle) {
            return $value->value();
        }

        return (string) $value;
    }
}
