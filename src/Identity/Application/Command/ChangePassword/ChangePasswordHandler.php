<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\ChangePassword;

use App\Identity\Application\Port\PasswordHasher;
use App\Identity\Domain\Exception\UserNotFoundException;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Port\EventDispatcher;
use App\Identity\Domain\Port\UserRepository;

final readonly class ChangePasswordHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private PasswordHasher $passwordHasher,
        private EventDispatcher $eventDispatcher,
    ) {}

    public function __invoke(ChangePasswordCommand $command): void
    {
        $userId = new UserId($command->userId);

        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            throw UserNotFoundException::withId($userId);
        }

        $user->changePassword($this->passwordHasher->hash($command->newPlainPassword));

        $this->userRepository->save($user);
        $this->eventDispatcher->dispatch($user->releaseEvents());
    }
}
