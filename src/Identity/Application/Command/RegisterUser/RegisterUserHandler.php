<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\RegisterUser;

use App\Identity\Application\DTO\UserDTO;
use App\Identity\Application\Port\EmailNotifier;
use App\Identity\Application\Port\PasswordHasher;
use App\Identity\Domain\Exception\EmailAlreadyTakenException;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\User;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Model\Username;
use App\Identity\Domain\Port\EventDispatcher;
use App\Identity\Domain\Port\UserRepository;

final readonly class RegisterUserHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private PasswordHasher $passwordHasher,
        private EventDispatcher $eventDispatcher,
        private EmailNotifier $emailNotifier,
    ) {}

    public function __invoke(RegisterUserCommand $command): UserDTO
    {
        $email = new Email($command->email);
        $username = new Username($command->username);

        if ($this->userRepository->findByEmail($email) !== null) {
            throw EmailAlreadyTakenException::fromEmail($email);
        }

        $user = User::register(
            UserId::generate(),
            $email,
            $username,
            $this->passwordHasher->hash($command->plainPassword),
        );

        $this->userRepository->save($user);
        $this->eventDispatcher->dispatch($user->releaseEvents());
        $this->emailNotifier->sendWelcome($email, $username->value());

        return UserDTO::fromUser($user);
    }
}
