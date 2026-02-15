<?php

declare(strict_types=1);

namespace App\Tests\Application\Identity\Command;

use App\Identity\Application\Command\ChangePassword\ChangePasswordCommand;
use App\Identity\Application\Command\ChangePassword\ChangePasswordHandler;
use App\Identity\Application\Port\PasswordHasher;
use App\Identity\Domain\Exception\UserNotFoundException;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\HashedPassword;
use App\Identity\Domain\Model\User;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Model\Username;
use App\Identity\Domain\Port\EventDispatcher;
use App\Identity\Domain\Port\UserRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ChangePasswordHandlerTest extends TestCase
{
    private const UUID = '550e8400-e29b-41d4-a716-446655440000';

    #[Test]
    public function it_changes_user_password(): void
    {
        $user = User::register(
            new UserId(self::UUID),
            new Email('user@example.com'),
            new Username('john'),
            new HashedPassword('old_hash'),
        );
        $user->releaseEvents();

        $repository = $this->createMock(UserRepository::class);
        $repository->method('findById')->willReturn($user);
        $repository->expects(self::once())->method('save');

        $hasher = $this->createStub(PasswordHasher::class);
        $hasher->method('hash')->willReturn(new HashedPassword('new_hash'));

        $dispatcher = $this->createMock(EventDispatcher::class);
        $dispatcher->expects(self::once())->method('dispatch');

        $handler = new ChangePasswordHandler($repository, $hasher, $dispatcher);

        $handler(new ChangePasswordCommand(self::UUID, 'new_plain_password'));
    }

    #[Test]
    public function it_throws_when_user_not_found(): void
    {
        $repository = $this->createStub(UserRepository::class);
        $repository->method('findById')->willReturn(null);

        $hasher = $this->createStub(PasswordHasher::class);
        $dispatcher = $this->createStub(EventDispatcher::class);

        $handler = new ChangePasswordHandler($repository, $hasher, $dispatcher);

        $this->expectException(UserNotFoundException::class);

        $handler(new ChangePasswordCommand(self::UUID, 'new_plain_password'));
    }
}
