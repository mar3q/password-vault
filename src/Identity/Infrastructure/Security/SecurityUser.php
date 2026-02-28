<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Security;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class SecurityUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @param string[] $roles
     */
    public function __construct(
        private string $id,
        private string $email,
        private string $password,
        private array $roles = ['ROLE_USER'],
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    /** @return string[] */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function eraseCredentials(): void {}
}
