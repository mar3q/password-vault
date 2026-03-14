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
use App\Vault\Presentation\Console\GetPasswordCommand;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class GetPasswordCommandTest extends TestCase
{
    #[Test]
    public function it_outputs_password(): void
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
            new EntryDTO('id-1', 'GitHub', 'john', 'secret123', 'https://github.com', null, '2025-01-01T00:00:00+00:00', '2025-01-01T00:00:00+00:00'),
            new EntryDTO('id-2', 'GitLab', 'jane', 'other_pass', 'https://gitlab.com', null, '2025-01-01T00:00:00+00:00', '2025-01-01T00:00:00+00:00'),
        ];

        $queryBus = $this->createStub(QueryBus::class);
        $queryBus->method('ask')->willReturn($entries);

        $tester = new CommandTester(new GetPasswordCommand($userRepository, $queryBus));
        $tester->execute(['--user' => 'john@example.com', '--title' => 'GitHub']);

        self::assertSame(0, $tester->getStatusCode());
        self::assertSame('secret123', $tester->getDisplay());
    }

    #[Test]
    public function it_reports_no_match(): void
    {
        $user = User::register(
            UserId::generate(),
            new Email('john@example.com'),
            new Username('john'),
            new HashedPassword('hashed'),
        );

        $userRepository = $this->createStub(UserRepository::class);
        $userRepository->method('findByEmail')->willReturn($user);

        $queryBus = $this->createStub(QueryBus::class);
        $queryBus->method('ask')->willReturn([]);

        $tester = new CommandTester(new GetPasswordCommand($userRepository, $queryBus));
        $tester->execute(['--user' => 'john@example.com', '--title' => 'NonExistent']);

        self::assertSame(1, $tester->getStatusCode());
        self::assertStringContainsString('No entry found', $tester->getDisplay());
    }

    #[Test]
    public function it_reports_user_not_found(): void
    {
        $userRepository = $this->createStub(UserRepository::class);
        $userRepository->method('findByEmail')->willReturn(null);

        $queryBus = $this->createStub(QueryBus::class);

        $tester = new CommandTester(new GetPasswordCommand($userRepository, $queryBus));
        $tester->execute(['--user' => 'unknown@example.com', '--title' => 'GitHub']);

        self::assertSame(1, $tester->getStatusCode());
        self::assertStringContainsString('not found', $tester->getDisplay());
    }
}
