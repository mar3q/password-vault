<?php

declare(strict_types=1);

namespace App\Tests\Application\Vault\Query;

use App\Vault\Application\DTO\EntryDTO;
use App\Vault\Application\Query\ListEntries\ListEntriesHandler;
use App\Vault\Application\Query\ListEntries\ListEntriesQuery;
use App\Vault\Domain\Model\EntryId;
use App\Vault\Domain\Model\EntryTitle;
use App\Vault\Domain\Model\OwnerId;
use App\Vault\Domain\Model\VaultEntry;
use App\Vault\Domain\Port\Encrypter;
use App\Vault\Domain\Port\VaultRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class ListEntriesHandlerTest extends TestCase
{
    #[Test]
    public function it_returns_entry_dtos_for_owner(): void
    {
        $ownerId = new OwnerId(Uuid::v4()->toRfc4122());

        $entry1 = VaultEntry::create(
            EntryId::generate(),
            $ownerId,
            new EntryTitle('GitHub'),
            'encrypted_1',
            'john',
            'https://github.com',
            null,
        );
        $entry2 = VaultEntry::create(
            EntryId::generate(),
            $ownerId,
            new EntryTitle('GitLab'),
            'encrypted_2',
            'jane',
            'https://gitlab.com',
            'notes',
        );

        $repository = $this->createStub(VaultRepository::class);
        $repository->method('findByOwnerId')->willReturn([$entry1, $entry2]);

        $encrypter = $this->createStub(Encrypter::class);
        $encrypter->method('decrypt')->willReturnMap([
            ['encrypted_1', 'plain_1'],
            ['encrypted_2', 'plain_2'],
        ]);

        $handler = new ListEntriesHandler($repository, $encrypter);

        $result = $handler(new ListEntriesQuery($ownerId->value()));

        self::assertCount(2, $result);
        self::assertSame('GitHub', $result[0]->title);
        self::assertSame('plain_1', $result[0]->password);
        self::assertSame('GitLab', $result[1]->title);
        self::assertSame('plain_2', $result[1]->password);
    }

    #[Test]
    public function it_returns_empty_array_when_no_entries(): void
    {
        $repository = $this->createStub(VaultRepository::class);
        $repository->method('findByOwnerId')->willReturn([]);

        $encrypter = $this->createStub(Encrypter::class);

        $handler = new ListEntriesHandler($repository, $encrypter);

        $result = $handler(new ListEntriesQuery(new OwnerId(Uuid::v4()->toRfc4122())->value()));

        self::assertSame([], $result);
    }
}
