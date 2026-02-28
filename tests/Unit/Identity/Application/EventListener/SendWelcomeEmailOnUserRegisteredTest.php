<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Application\EventListener;

use App\Identity\Application\EventListener\SendWelcomeEmailOnUserRegistered;
use App\Identity\Application\Port\EmailNotifier;
use App\Identity\Domain\Event\UserRegistered;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Model\Username;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SendWelcomeEmailOnUserRegisteredTest extends TestCase
{
    #[Test]
    public function it_sends_welcome_email_on_user_registered(): void
    {
        $notifier = $this->createMock(EmailNotifier::class);
        $notifier->expects(self::once())->method('sendWelcome');

        $listener = new SendWelcomeEmailOnUserRegistered($notifier);

        $event = new UserRegistered(
            UserId::generate(),
            new Email('user@example.com'),
            new Username('john'),
        );

        $listener($event);
    }
}
