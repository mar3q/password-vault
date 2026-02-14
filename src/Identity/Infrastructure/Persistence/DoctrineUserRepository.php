<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Persistence;

use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\User;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Port\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineUserRepository implements UserRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function findById(UserId $id): ?User
    {
        return $this->entityManager->find(User::class, $id->value());
    }

    public function findByEmail(Email $email): ?User
    {
        return $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $email->value()]);
    }

    public function save(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
