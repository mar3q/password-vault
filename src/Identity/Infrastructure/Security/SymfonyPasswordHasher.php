<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Security;

use App\Identity\Application\Port\PasswordHasher;
use App\Identity\Domain\Model\HashedPassword;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;

final readonly class SymfonyPasswordHasher implements PasswordHasher
{
    private PasswordHasherFactory $factory;

    public function __construct()
    {
        $this->factory = new PasswordHasherFactory([
            'default' => ['algorithm' => 'auto'],
        ]);
    }

    public function hash(string $plainPassword): HashedPassword
    {
        $hasher = $this->factory->getPasswordHasher('default');

        return new HashedPassword($hasher->hash($plainPassword));
    }
}
