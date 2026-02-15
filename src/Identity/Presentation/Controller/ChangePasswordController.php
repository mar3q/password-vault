<?php

declare(strict_types=1);

namespace App\Identity\Presentation\Controller;

use App\Identity\Application\Command\ChangePassword\ChangePasswordCommand;
use App\Identity\Application\Command\ChangePassword\ChangePasswordHandler;
use App\Identity\Domain\Exception\UserNotFoundException;
use App\Shared\Infrastructure\Http\MalformedJsonException;
use App\Shared\Infrastructure\Http\RequestPayload;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users/{id}/password', methods: ['PATCH'])]
#[OA\Patch(
    summary: 'Change user password',
    description: 'Updates the password for the given user. The password is hashed before storage.',
    tags: ['Users'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['password'],
            properties: [
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'newSecret456'),
            ],
        ),
    ),
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
    response: Response::HTTP_NO_CONTENT,
    description: 'Password changed successfully.',
)]
#[OA\Response(
    response: Response::HTTP_BAD_REQUEST,
    description: 'Invalid UUID or missing password field.',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'error', type: 'string', example: 'Field "password" is required.'),
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
final readonly class ChangePasswordController
{
    public function __construct(
        private ChangePasswordHandler $handler,
    ) {}

    public function __invoke(string $id, Request $request): JsonResponse
    {
        try {
            $data = RequestPayload::jsonDecode($request);
        } catch (MalformedJsonException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $password = (string) ($data['password'] ?? '');

        if ($password === '') {
            return new JsonResponse(
                ['error' => 'Field "password" is required.'],
                Response::HTTP_BAD_REQUEST,
            );
        }

        try {
            ($this->handler)(new ChangePasswordCommand($id, $password));
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (UserNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
