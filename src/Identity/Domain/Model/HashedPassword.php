<?php

declare(strict_types=1);

namespace App\Identity\Domain\Model;

final readonly class HashedPassword
{
    private string $value;

    public function __construct(string $hash)
    {
        if ($hash === '') {
            throw new \InvalidArgumentException('Password hash cannot be empty.');
        }

        $this->value = $hash;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
