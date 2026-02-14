<?php

declare(strict_types=1);

namespace App\Identity\Presentation\Controller;

use App\Identity\Application\Command\RegisterUser\RegisterUserCommand;
use App\Identity\Application\Command\RegisterUser\RegisterUserHandler;
use App\Identity\Domain\Exception\EmailAlreadyTakenException;
use App\Identity\Domain\Exception\InvalidEmailException;
use App\Identity\Domain\Exception\InvalidUsernameException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users', methods: ['POST'])]
#[OA\Post(
    summary: 'Register a new user',
    description: 'Creates a new user account with the given email, username and password.',
    tags: ['Users'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'username', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                new OA\Property(property: 'username', type: 'string', minLength: 3, maxLength: 64, example: 'john_doe'),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret123'),
            ],
        ),
    ),
)]
#[OA\Response(
    response: Response::HTTP_CREATED,
    description: 'User registered successfully.',
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
    description: 'Validation error (missing fields, invalid email or username).',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'error', type: 'string', example: 'Fields "email", "username" and "password" are required.'),
        ],
    ),
)]
#[OA\Response(
    response: Response::HTTP_CONFLICT,
    description: 'Email already taken.',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'error', type: 'string', example: 'Email "john@example.com" is already taken.'),
        ],
    ),
)]
final readonly class RegisterUserController
{
    public function __construct(
        private RegisterUserHandler $handler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        /** @var array{email?: string, username?: string, password?: string} $data */
        $data = json_decode($request->getContent(), true) ?: [];

        $email = $data['email'] ?? '';
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if ($email === '' || $username === '' || $password === '') {
            return new JsonResponse(
                ['error' => 'Fields "email", "username" and "password" are required.'],
                Response::HTTP_BAD_REQUEST,
            );
        }

        try {
            $userDTO = ($this->handler)(new RegisterUserCommand($email, $username, $password));
        } catch (InvalidEmailException|InvalidUsernameException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (EmailAlreadyTakenException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }

        return new JsonResponse([
            'id' => $userDTO->id,
            'email' => $userDTO->email,
            'username' => $userDTO->username,
        ], Response::HTTP_CREATED);
    }
}
