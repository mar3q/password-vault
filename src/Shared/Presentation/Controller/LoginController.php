<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Controller;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth/login', methods: ['POST'])]
#[OA\Post(
    summary: 'Authenticate and obtain a JWT token',
    description: 'Provide email and password to receive a JWT bearer token.',
    tags: ['Auth'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['username', 'password'],
            properties: [
                new OA\Property(property: 'username', type: 'string', format: 'email', example: 'john@example.com', description: 'User email address'),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret123'),
            ],
        ),
    ),
)]
#[OA\Response(
    response: Response::HTTP_OK,
    description: 'Authentication successful.',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...'),
        ],
    ),
)]
#[OA\Response(
    response: Response::HTTP_UNAUTHORIZED,
    description: 'Invalid credentials.',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'code', type: 'integer', example: 401),
            new OA\Property(property: 'message', type: 'string', example: 'Invalid credentials.'),
        ],
    ),
)]
final readonly class LoginController
{
    public function __invoke(): JsonResponse
    {
        // This controller is never executed — the json_login firewall intercepts the request.
        // It exists solely to provide OpenAPI documentation for Swagger UI.
        throw new \LogicException('This should never be reached.');
    }
}
