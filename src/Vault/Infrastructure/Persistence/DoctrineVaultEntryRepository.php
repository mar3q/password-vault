<?php

declare(strict_types=1);

namespace App\Vault\Infrastructure\Persistence;

use App\Vault\Domain\Model\EntryId;
use App\Vault\Domain\Model\OwnerId;
use App\Vault\Domain\Model\VaultEntry;
use App\Vault\Domain\Port\VaultRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineVaultEntryRepository implements VaultRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function findById(EntryId $id): ?VaultEntry
    {
        return $this->entityManager->find(VaultEntry::class, $id->value());
    }

    /** @return VaultEntry[] */
    public function findByOwnerId(OwnerId $ownerId): array
    {
        return $this->entityManager->getRepository(VaultEntry::class)
            ->findBy(['ownerId' => $ownerId->value()], ['createdAt' => 'DESC']);
    }

    public function save(VaultEntry $entry): void
    {
        $this->entityManager->persist($entry);
        $this->entityManager->flush();
    }

    public function delete(VaultEntry $entry): void
    {
        $this->entityManager->remove($entry);
        $this->entityManager->flush();
    }
}
