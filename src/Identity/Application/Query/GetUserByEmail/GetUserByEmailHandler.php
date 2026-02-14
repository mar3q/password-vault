<?php

declare(strict_types=1);

namespace App\Identity\Application\Query\GetUserByEmail;

use App\Identity\Application\DTO\UserDTO;
use App\Identity\Domain\Exception\UserNotFoundException;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Port\UserRepository;

final readonly class GetUserByEmailHandler
{
    public function __construct(
        private UserRepository $userRepository,
    ) {}

    public function __invoke(GetUserByEmailQuery $query): UserDTO
    {
        $email = new Email($query->email);

        $user = $this->userRepository->findByEmail($email);

        if ($user === null) {
            throw UserNotFoundException::withEmail($query->email);
        }

        return UserDTO::fromUser($user);
    }
}
