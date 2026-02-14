<?php

declare(strict_types=1);

namespace App\Identity\Presentation\Controller;

use App\Identity\Application\Query\GetUserById\GetUserByIdHandler;
use App\Identity\Application\Query\GetUserById\GetUserByIdQuery;
use App\Identity\Domain\Exception\UserNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users/{id}', methods: ['GET'])]
final readonly class GetUserController
{
    public function __construct(
        private GetUserByIdHandler $handler,
    ) {}

    public function __invoke(string $id): JsonResponse
    {
        try {
            $userDTO = ($this->handler)(new GetUserByIdQuery($id));
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (UserNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id' => $userDTO->id,
            'email' => $userDTO->email,
            'username' => $userDTO->username,
        ]);
    }
}
