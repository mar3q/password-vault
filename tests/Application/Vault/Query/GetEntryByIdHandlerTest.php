<?php

declare(strict_types=1);

namespace App\Tests\Application\Vault\Query;

use App\Vault\Application\DTO\EntryDTO;
use App\Vault\Application\Query\GetEntryById\GetEntryByIdHandler;
use App\Vault\Application\Query\GetEntryById\GetEntryByIdQuery;
use App\Vault\Domain\Exception\AccessDeniedException;
use App\Vault\Domain\Exception\EntryNotFoundException;
use App\Vault\Domain\Model\EntryId;
use App\Vault\Domain\Model\EntryTitle;
use App\Vault\Domain\Model\OwnerId;
use App\Vault\Domain\Model\VaultEntry;
use App\Vault\Domain\Port\Encrypter;
use App\Vault\Domain\Port\VaultRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class GetEntryByIdHandlerTest extends TestCase
{
    #[Test]
    public function it_returns_entry_dto_with_decrypted_password(): void
    {
        $entryId = EntryId::generate();
        $ownerId = new OwnerId(Uuid::v4()->toRfc4122());
        $entry = VaultEntry::create(
            $entryId,
            $ownerId,
            new EntryTitle('GitHub'),
            'encrypted_password',
            'john',
            'https://github.com',
            'my notes',
        );

        $repository = $this->createStub(VaultRepository::class);
        $repository->method('findById')->willReturn($entry);

        $encrypter = $this->createStub(Encrypter::class);
        $encrypter->method('decrypt')->willReturn('plain_password');

        $handler = new GetEntryByIdHandler($repository, $encrypter);

        $dto = $handler(new GetEntryByIdQuery($entryId->value(), $ownerId->value()));

        self::assertInstanceOf(EntryDTO::class, $dto);
        self::assertSame($entryId->value(), $dto->id);
        self::assertSame('GitHub', $dto->title);
        self::assertSame('john', $dto->login);
        self::assertSame('plain_password', $dto->password);
        self::assertSame('https://github.com', $dto->url);
        self::assertSame('my notes', $dto->notes);
    }

    #[Test]
    public function it_throws_when_entry_not_found(): void
    {
        $repository = $this->createStub(VaultRepository::class);
        $repository->method('findById')->willReturn(null);

        $encrypter = $this->createStub(Encrypter::class);

        $handler = new GetEntryByIdHandler($repository, $encrypter);

        $this->expectException(EntryNotFoundException::class);

        $handler(new GetEntryByIdQuery(
            EntryId::generate()->value(),
            new OwnerId(Uuid::v4()->toRfc4122())->value(),
        ));
    }

    #[Test]
    public function it_throws_when_owner_does_not_match(): void
    {
        $entryId = EntryId::generate();
        $ownerId = new OwnerId(Uuid::v4()->toRfc4122());
        $differentOwnerId = new OwnerId(Uuid::v4()->toRfc4122());

        $entry = VaultEntry::create(
            $entryId,
            $ownerId,
            new EntryTitle('GitHub'),
            'encrypted_password',
            null,
            null,
            null,
        );

        $repository = $this->createStub(VaultRepository::class);
        $repository->method('findById')->willReturn($entry);

        $encrypter = $this->createStub(Encrypter::class);

        $handler = new GetEntryByIdHandler($repository, $encrypter);

        $this->expectException(AccessDeniedException::class);

        $handler(new GetEntryByIdQuery($entryId->value(), $differentOwnerId->value()));
    }
}
