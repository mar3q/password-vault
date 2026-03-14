<?php

declare(strict_types=1);

namespace App\Tests\Application\Vault\Console;

use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\HashedPassword;
use App\Identity\Domain\Model\User;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Model\Username;
use App\Identity\Domain\Port\UserRepository;
use App\Shared\Application\Port\QueryBus;
use App\Vault\Application\DTO\EntryDTO;
use App\Vault\Presentation\Console\ExportEntriesCommand;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class ExportEntriesCommandTest extends TestCase
{
    private ?string $tempFile = null;

    protected function tearDown(): void
    {
        if ($this->tempFile !== null && file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    #[Test]
    public function it_exports_json_to_stdout(): void
    {
        $user = User::register(
            UserId::generate(),
            new Email('john@example.com'),
            new Username('john'),
            new HashedPassword('hashed'),
        );

        $userRepository = $this->createStub(UserRepository::class);
        $userRepository->method('findByEmail')->willReturn($user);

        $entries = [
            new EntryDTO('id-1', 'GitHub', 'john', 'secret', 'https://github.com', null, '2025-01-01T00:00:00+00:00', '2025-01-01T00:00:00+00:00'),
        ];

        $queryBus = $this->createStub(QueryBus::class);
        $queryBus->method('ask')->willReturn($entries);

        $tester = new CommandTester(new ExportEntriesCommand($userRepository, $queryBus));
        $tester->execute(['--user' => 'john@example.com']);

        self::assertSame(0, $tester->getStatusCode());

        $decoded = json_decode($tester->getDisplay(), true);
        self::assertIsArray($decoded);
        self::assertCount(1, $decoded);
        self::assertSame('GitHub', $decoded[0]['title']);
        self::assertSame('secret', $decoded[0]['password']);
    }

    #[Test]
    public function it_exports_json_to_file(): void
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'vault_export_');

        $user = User::register(
            UserId::generate(),
            new Email('john@example.com'),
            new Username('john'),
            new HashedPassword('hashed'),
        );

        $userRepository = $this->createStub(UserRepository::class);
        $userRepository->method('findByEmail')->willReturn($user);

        $entries = [
            new EntryDTO('id-1', 'GitHub', 'john', 'secret', 'https://github.com', null, '2025-01-01T00:00:00+00:00', '2025-01-01T00:00:00+00:00'),
        ];

        $queryBus = $this->createStub(QueryBus::class);
        $queryBus->method('ask')->willReturn($entries);

        $tester = new CommandTester(new ExportEntriesCommand($userRepository, $queryBus));
        $tester->execute(['--user' => 'john@example.com', '--output' => $this->tempFile]);

        self::assertSame(0, $tester->getStatusCode());
        self::assertFileExists($this->tempFile);

        $decoded = json_decode(file_get_contents($this->tempFile), true);
        self::assertIsArray($decoded);
        self::assertCount(1, $decoded);
        self::assertSame('GitHub', $decoded[0]['title']);
    }

    #[Test]
    public function it_reports_user_not_found(): void
    {
        $userRepository = $this->createStub(UserRepository::class);
        $userRepository->method('findByEmail')->willReturn(null);

        $queryBus = $this->createStub(QueryBus::class);

        $tester = new CommandTester(new ExportEntriesCommand($userRepository, $queryBus));
        $tester->execute(['--user' => 'unknown@example.com']);

        self::assertSame(1, $tester->getStatusCode());
        self::assertStringContainsString('not found', $tester->getDisplay());
    }
}
