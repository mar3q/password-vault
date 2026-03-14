<?php

declare(strict_types=1);

namespace App\Tests\Application\Identity\Console;

use App\Identity\Application\DTO\UserDTO;
use App\Identity\Domain\Exception\EmailAlreadyTakenException;
use App\Identity\Domain\Model\Email;
use App\Identity\Presentation\Console\CreateUserCommand;
use App\Shared\Application\Port\CommandBus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class CreateUserCommandTest extends TestCase
{
    #[Test]
    public function it_creates_user(): void
    {
        $dto = new UserDTO('550e8400-e29b-41d4-a716-446655440000', 'john@example.com', 'john');

        $commandBus = $this->createStub(CommandBus::class);
        $commandBus->method('dispatch')->willReturn($dto);

        $tester = new CommandTester(new CreateUserCommand($commandBus));
        $tester->execute([
            'email' => 'john@example.com',
            'username' => 'john',
            'password' => 'secret',
        ]);

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString('550e8400-e29b-41d4-a716-446655440000', $tester->getDisplay());
    }

    #[Test]
    public function it_handles_duplicate_email(): void
    {
        $commandBus = $this->createStub(CommandBus::class);
        $commandBus->method('dispatch')->willThrowException(
            EmailAlreadyTakenException::fromEmail(new Email('john@example.com')),
        );

        $tester = new CommandTester(new CreateUserCommand($commandBus));
        $tester->execute([
            'email' => 'john@example.com',
            'username' => 'john',
            'password' => 'secret',
        ]);

        self::assertSame(1, $tester->getStatusCode());
        self::assertStringContainsString('already taken', $tester->getDisplay());
    }
}
