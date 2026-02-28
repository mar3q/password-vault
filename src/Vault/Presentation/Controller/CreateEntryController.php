<?php

declare(strict_types=1);

namespace App\Vault\Presentation\Controller;

use App\Identity\Infrastructure\Security\SecurityUser;
use App\Shared\Infrastructure\Http\MalformedJsonException;
use App\Shared\Infrastructure\Http\RequestPayload;
use App\Vault\Application\Command\CreateEntry\CreateEntryCommand;
use App\Vault\Application\Command\CreateEntry\CreateEntryHandler;
use App\Vault\Domain\Exception\InvalidEntryTitleException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/vault', methods: ['POST'])]
#[OA\Post(
    summary: 'Create a vault entry',
    description: 'Stores a new encrypted password entry in the vault.',
    security: [['Bearer' => []]],
    tags: ['Vault'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['title', 'password'],
            properties: [
                new OA\Property(property: 'title', type: 'string', example: 'GitHub'),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'my-secret'),
                new OA\Property(property: 'login', type: 'string', example: 'john@example.com'),
                new OA\Property(property: 'url', type: 'string', example: 'https://github.com'),
                new OA\Property(property: 'notes', type: 'string', example: 'Work account'),
            ],
        ),
    ),
)]
#[OA\Response(response: Response::HTTP_CREATED, description: 'Entry created.')]
#[OA\Response(response: Response::HTTP_BAD_REQUEST, description: 'Validation error.')]
final readonly class CreateEntryController
{
    public function __construct(
        private CreateEntryHandler $handler,
        private Security $security,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = RequestPayload::jsonDecode($request);
        } catch (MalformedJsonException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $title = (string) ($data['title'] ?? '');
        $password = (string) ($data['password'] ?? '');

        if ($title === '' || $password === '') {
            return new JsonResponse(
                ['error' => 'Fields "title" and "password" are required.'],
                Response::HTTP_BAD_REQUEST,
            );
        }

        /** @var SecurityUser $user */
        $user = $this->security->getUser();

        try {
            $dto = ($this->handler)(new CreateEntryCommand(
                $user->id(),
                $title,
                $password,
                isset($data['login']) ? (string) $data['login'] : null,
                isset($data['url']) ? (string) $data['url'] : null,
                isset($data['notes']) ? (string) $data['notes'] : null,
            ));
        } catch (InvalidEntryTitleException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'id' => $dto->id,
            'title' => $dto->title,
            'login' => $dto->login,
            'password' => $dto->password,
            'url' => $dto->url,
            'notes' => $dto->notes,
            'createdAt' => $dto->createdAt,
            'updatedAt' => $dto->updatedAt,
        ], Response::HTTP_CREATED);
    }
}
