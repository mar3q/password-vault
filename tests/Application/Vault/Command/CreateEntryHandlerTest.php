<?php

declare(strict_types=1);

namespace App\Tests\Application\Vault\Command;

use App\Identity\Domain\Port\EventDispatcher;
use App\Vault\Application\Command\CreateEntry\CreateEntryCommand;
use App\Vault\Application\Command\CreateEntry\CreateEntryHandler;
use App\Vault\Application\DTO\EntryDTO;
use App\Vault\Domain\Port\Encrypter;
use App\Vault\Domain\Port\VaultRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class CreateEntryHandlerTest extends TestCase
{
    #[Test]
    public function it_creates_entry_and_returns_dto(): void
    {
        $ownerId = Uuid::v4()->toRfc4122();

        $repository = $this->createMock(VaultRepository::class);
        $repository->expects(self::once())->method('save');

        $encrypter = $this->createStub(Encrypter::class);
        $encrypter->method('encrypt')->willReturn('encrypted');

        $dispatcher = $this->createMock(EventDispatcher::class);
        $dispatcher->expects(self::once())->method('dispatch');

        $handler = new CreateEntryHandler($repository, $encrypter, $dispatcher);

        $dto = $handler(new CreateEntryCommand(
            $ownerId,
            'GitHub',
            'secret123',
            'john',
            'https://github.com',
            'my notes',
        ));

        self::assertInstanceOf(EntryDTO::class, $dto);
        self::assertNotEmpty($dto->id);
        self::assertSame('GitHub', $dto->title);
        self::assertSame('john', $dto->login);
        self::assertSame('secret123', $dto->password);
        self::assertSame('https://github.com', $dto->url);
        self::assertSame('my notes', $dto->notes);
    }
}
