<?php

declare(strict_types=1);

namespace App\Tests\Application\Identity\Command;

use App\Identity\Application\Command\RegisterUser\RegisterUserCommand;
use App\Identity\Application\Command\RegisterUser\RegisterUserHandler;
use App\Identity\Application\Port\PasswordHasher;
use App\Identity\Domain\Exception\EmailAlreadyTakenException;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\HashedPassword;
use App\Identity\Domain\Model\User;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Model\Username;
use App\Identity\Domain\Port\EventDispatcher;
use App\Identity\Domain\Port\UserRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RegisterUserHandlerTest extends TestCase
{
    #[Test]
    public function it_registers_a_new_user(): void
    {
        $repository = $this->createMock(UserRepository::class);
        $repository->method('findByEmail')->willReturn(null);
        $repository->expects(self::once())->method('save');

        $hasher = $this->createStub(PasswordHasher::class);
        $hasher->method('hash')->willReturn(new HashedPassword('hashed'));

        $dispatcher = $this->createMock(EventDispatcher::class);
        $dispatcher->expects(self::once())->method('dispatch');

        $handler = new RegisterUserHandler($repository, $hasher, $dispatcher);

        $dto = $handler(new RegisterUserCommand('user@example.com', 'john', 'plain_password'));

        self::assertSame('user@example.com', $dto->email);
        self::assertSame('john', $dto->username);
        self::assertNotEmpty($dto->id);
    }

    #[Test]
    public function it_throws_when_email_already_taken(): void
    {
        $existingUser = User::register(
            UserId::generate(),
            new Email('user@example.com'),
            new Username('existing'),
            new HashedPassword('hashed'),
        );

        $repository = $this->createStub(UserRepository::class);
        $repository->method('findByEmail')->willReturn($existingUser);

        $hasher = $this->createStub(PasswordHasher::class);
        $dispatcher = $this->createStub(EventDispatcher::class);

        $handler = new RegisterUserHandler($repository, $hasher, $dispatcher);

        $this->expectException(EmailAlreadyTakenException::class);

        $handler(new RegisterUserCommand('user@example.com', 'john', 'plain_password'));
    }
}
