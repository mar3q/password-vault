<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Persistence\Type;

use App\Identity\Domain\Model\UserId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class UserIdType extends StringType
{
    public const string NAME = 'user_id';

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?UserId
    {
        if ($value === null) {
            return null;
        }

        return new UserId((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof UserId) {
            return $value->value();
        }

        return (string) $value;
    }
}
