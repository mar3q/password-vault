<?php

declare(strict_types=1);

namespace App\Vault\Infrastructure\Persistence\Type;

use App\Vault\Domain\Model\OwnerId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class OwnerIdType extends StringType
{
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?OwnerId
    {
        if ($value === null) {
            return null;
        }

        return new OwnerId((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof OwnerId) {
            return $value->value();
        }

        return (string) $value;
    }
}
