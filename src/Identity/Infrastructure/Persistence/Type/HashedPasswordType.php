<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Persistence\Type;

use App\Identity\Domain\Model\HashedPassword;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class HashedPasswordType extends StringType
{
    public const string NAME = 'hashed_password';

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?HashedPassword
    {
        if ($value === null) {
            return null;
        }

        return new HashedPassword((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof HashedPassword) {
            return $value->value();
        }

        return (string) $value;
    }
}
