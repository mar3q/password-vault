<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Domain\Model;

use App\Identity\Domain\Event\UserEmailChanged;
use App\Identity\Domain\Event\UserPasswordChanged;
use App\Identity\Domain\Event\UserRegistered;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\HashedPassword;
use App\Identity\Domain\Model\User;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Model\Username;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    #[Test]
    public function it_registers_a_new_user(): void
    {
        $user = $this->createUser();

        self::assertSame('user@example.com', $user->email()->value());
        self::assertSame('john', $user->username()->value());
    }

    #[Test]
    public function it_records_user_registered_event(): void
    {
        $user = $this->createUser();

        $events = $user->releaseEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(UserRegistered::class, $events[0]);
    }

    #[Test]
    public function it_clears_events_after_release(): void
    {
        $user = $this->createUser();

        $user->releaseEvents();

        self::assertCount(0, $user->releaseEvents());
    }

    #[Test]
    public function it_changes_email(): void
    {
        $user = $this->createUser();
        $user->releaseEvents();

        $user->changeEmail(new Email('new@example.com'));

        self::assertSame('new@example.com', $user->email()->value());

        $events = $user->releaseEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(UserEmailChanged::class, $events[0]);
    }

    #[Test]
    public function it_does_not_record_event_when_email_unchanged(): void
    {
        $user = $this->createUser();
        $user->releaseEvents();

        $user->changeEmail(new Email('user@example.com'));

        self::assertCount(0, $user->releaseEvents());
    }

    #[Test]
    public function it_changes_password(): void
    {
        $user = $this->createUser();
        $user->releaseEvents();

        $newPassword = new HashedPassword('new_hashed_pw');
        $user->changePassword($newPassword);

        self::assertSame('new_hashed_pw', $user->password()->value());

        $events = $user->releaseEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(UserPasswordChanged::class, $events[0]);
    }

    private function createUser(): User
    {
        return User::register(
            UserId::generate(),
            new Email('user@example.com'),
            new Username('john'),
            new HashedPassword('hashed_password'),
        );
    }
}
