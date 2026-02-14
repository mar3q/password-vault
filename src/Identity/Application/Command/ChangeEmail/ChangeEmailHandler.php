<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\ChangeEmail;

use App\Identity\Domain\Exception\EmailAlreadyTakenException;
use App\Identity\Domain\Exception\UserNotFoundException;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Port\EventDispatcher;
use App\Identity\Domain\Port\UserRepository;

final readonly class ChangeEmailHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private EventDispatcher $eventDispatcher,
    ) {}

    public function __invoke(ChangeEmailCommand $command): void
    {
        $userId = new UserId($command->userId);
        $newEmail = new Email($command->newEmail);

        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            throw UserNotFoundException::withId($userId);
        }

        $existing = $this->userRepository->findByEmail($newEmail);

        if ($existing !== null && !$existing->id()->equals($userId)) {
            throw EmailAlreadyTakenException::fromEmail($newEmail);
        }

        $user->changeEmail($newEmail);

        $this->userRepository->save($user);
        $this->eventDispatcher->dispatch($user->releaseEvents());
    }
}
