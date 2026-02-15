<?php

declare(strict_types=1);

namespace App\Tests\Application\Identity\Command;

use App\Identity\Application\Command\ChangeEmail\ChangeEmailCommand;
use App\Identity\Application\Command\ChangeEmail\ChangeEmailHandler;
use App\Identity\Domain\Exception\EmailAlreadyTakenException;
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

final class ChangeEmailHandlerTest extends TestCase
{
    private const UUID = '550e8400-e29b-41d4-a716-446655440000';

    #[Test]
    public function it_changes_user_email(): void
    {
        $user = User::register(
            new UserId(self::UUID),
            new Email('old@example.com'),
            new Username('john'),
            new HashedPassword('hashed'),
        );
        $user->releaseEvents();

        $repository = $this->createMock(UserRepository::class);
        $repository->method('findById')->willReturn($user);
        $repository->method('findByEmail')->willReturn(null);
        $repository->expects(self::once())->method('save');

        $dispatcher = $this->createMock(EventDispatcher::class);
        $dispatcher->expects(self::once())->method('dispatch');

        $handler = new ChangeEmailHandler($repository, $dispatcher);

        $handler(new ChangeEmailCommand(self::UUID, 'new@example.com'));

        self::assertSame('new@example.com', $user->email()->value());
    }

    #[Test]
    public function it_throws_when_user_not_found(): void
    {
        $repository = $this->createStub(UserRepository::class);
        $repository->method('findById')->willReturn(null);

        $dispatcher = $this->createStub(EventDispatcher::class);

        $handler = new ChangeEmailHandler($repository, $dispatcher);

        $this->expectException(UserNotFoundException::class);

        $handler(new ChangeEmailCommand(self::UUID, 'new@example.com'));
    }

    #[Test]
    public function it_throws_when_email_already_taken_by_another_user(): void
    {
        $user = User::register(
            new UserId(self::UUID),
            new Email('old@example.com'),
            new Username('john'),
            new HashedPassword('hashed'),
        );

        $otherUser = User::register(
            UserId::generate(),
            new Email('new@example.com'),
            new Username('jane'),
            new HashedPassword('hashed'),
        );

        $repository = $this->createStub(UserRepository::class);
        $repository->method('findById')->willReturn($user);
        $repository->method('findByEmail')->willReturn($otherUser);

        $dispatcher = $this->createStub(EventDispatcher::class);

        $handler = new ChangeEmailHandler($repository, $dispatcher);

        $this->expectException(EmailAlreadyTakenException::class);

        $handler(new ChangeEmailCommand(self::UUID, 'new@example.com'));
    }

    #[Test]
    public function it_allows_keeping_same_email(): void
    {
        $user = User::register(
            new UserId(self::UUID),
            new Email('same@example.com'),
            new Username('john'),
            new HashedPassword('hashed'),
        );
        $user->releaseEvents();

        $repository = $this->createMock(UserRepository::class);
        $repository->method('findById')->willReturn($user);
        $repository->method('findByEmail')->willReturn($user);
        $repository->expects(self::once())->method('save');

        $dispatcher = $this->createMock(EventDispatcher::class);
        $dispatcher->expects(self::once())->method('dispatch');

        $handler = new ChangeEmailHandler($repository, $dispatcher);

        $handler(new ChangeEmailCommand(self::UUID, 'same@example.com'));
    }
}
