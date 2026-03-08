<?php

declare(strict_types=1);

namespace App\Identity\Presentation\Controller;

use App\Identity\Application\DTO\UserDTO;
use App\Identity\Application\Query\GetUserById\GetUserByIdQuery;
use App\Identity\Domain\Exception\UserNotFoundException;
use App\Shared\Application\Port\QueryBus;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users/{id}', methods: ['GET'])]
#[OA\Get(
    summary: 'Get user by ID',
    description: 'Returns user details for the given UUID.',
    tags: ['Users'],
)]
#[OA\Parameter(
    name: 'id',
    description: 'User UUID',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'string', format: 'uuid'),
    example: '550e8400-e29b-41d4-a716-446655440000',
)]
#[OA\Response(
    response: Response::HTTP_OK,
    description: 'User found.',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
            new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
            new OA\Property(property: 'username', type: 'string', example: 'john_doe'),
        ],
    ),
)]
#[OA\Response(
    response: Response::HTTP_BAD_REQUEST,
    description: 'Invalid UUID format.',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'error', type: 'string', example: 'Invalid UUID: not-a-uuid'),
        ],
    ),
)]
#[OA\Response(
    response: Response::HTTP_NOT_FOUND,
    description: 'User not found.',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'error', type: 'string', example: 'User with ID "550e8400-e29b-41d4-a716-446655440000" not found.'),
        ],
    ),
)]
final readonly class GetUserController
{
    public function __construct(
        private QueryBus $queryBus,
    ) {}

    public function __invoke(string $id): JsonResponse
    {
        try {
            /** @var UserDTO $userDTO */
            $userDTO = $this->queryBus->ask(new GetUserByIdQuery($id));
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
