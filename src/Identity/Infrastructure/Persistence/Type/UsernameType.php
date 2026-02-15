<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Persistence\Type;

use App\Identity\Domain\Model\Username;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class UsernameType extends StringType
{
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Username
    {
        if ($value === null) {
            return null;
        }

        return new Username((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Username) {
            return $value->value();
        }

        return (string) $value;
    }
}
