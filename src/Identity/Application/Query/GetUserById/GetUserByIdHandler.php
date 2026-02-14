<?php

declare(strict_types=1);

namespace App\Identity\Application\Query\GetUserById;

use App\Identity\Application\DTO\UserDTO;
use App\Identity\Domain\Exception\UserNotFoundException;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Port\UserRepository;

final readonly class GetUserByIdHandler
{
    public function __construct(
        private UserRepository $userRepository,
    ) {}

    public function __invoke(GetUserByIdQuery $query): UserDTO
    {
        $userId = new UserId($query->userId);

        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            throw UserNotFoundException::withId($userId);
        }

        return UserDTO::fromUser($user);
    }
}
