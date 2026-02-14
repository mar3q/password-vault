<?php

declare(strict_types=1);

namespace App\Identity\Presentation\Controller;

use App\Identity\Application\Command\ChangeEmail\ChangeEmailCommand;
use App\Identity\Application\Command\ChangeEmail\ChangeEmailHandler;
use App\Identity\Domain\Exception\EmailAlreadyTakenException;
use App\Identity\Domain\Exception\InvalidEmailException;
use App\Identity\Domain\Exception\UserNotFoundException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users/{id}/email', methods: ['PATCH'])]
#[OA\Patch(
    summary: 'Change user email',
    description: 'Updates the email address for the given user. The new email must be unique.',
    tags: ['Users'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'new@example.com'),
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
    description: 'Email changed successfully.',
)]
#[OA\Response(
    response: Response::HTTP_BAD_REQUEST,
    description: 'Invalid UUID or email format.',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'error', type: 'string', example: 'Invalid email address: "not-an-email".'),
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
#[OA\Response(
    response: Response::HTTP_CONFLICT,
    description: 'Email already taken by another user.',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'error', type: 'string', example: 'Email "new@example.com" is already taken.'),
        ],
    ),
)]
final readonly class ChangeEmailController
{
    public function __construct(
        private ChangeEmailHandler $handler,
    ) {}

    public function __invoke(string $id, Request $request): JsonResponse
    {
        /** @var array{email?: string} $data */
        $data = json_decode($request->getContent(), true) ?: [];

        $email = $data['email'] ?? '';

        if ($email === '') {
            return new JsonResponse(
                ['error' => 'Field "email" is required.'],
                Response::HTTP_BAD_REQUEST,
            );
        }

        try {
            ($this->handler)(new ChangeEmailCommand($id, $email));
        } catch (\InvalidArgumentException|InvalidEmailException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (UserNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (EmailAlreadyTakenException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
