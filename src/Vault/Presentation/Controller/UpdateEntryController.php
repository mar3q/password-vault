<?php

declare(strict_types=1);

namespace App\Vault\Presentation\Controller;

use App\Identity\Infrastructure\Security\SecurityUser;
use App\Shared\Infrastructure\Http\MalformedJsonException;
use App\Shared\Infrastructure\Http\RequestPayload;
use App\Vault\Application\Command\UpdateEntry\UpdateEntryCommand;
use App\Vault\Application\Command\UpdateEntry\UpdateEntryHandler;
use App\Vault\Domain\Exception\AccessDeniedException;
use App\Vault\Domain\Exception\EntryNotFoundException;
use App\Vault\Domain\Exception\InvalidEntryTitleException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/vault/{id}', methods: ['PUT'])]
#[OA\Put(
    summary: 'Update a vault entry',
    description: 'Updates an existing vault entry.',
    security: [['Bearer' => []]],
    tags: ['Vault'],
)]
#[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
#[OA\Response(response: Response::HTTP_NO_CONTENT, description: 'Entry updated.')]
#[OA\Response(response: Response::HTTP_NOT_FOUND, description: 'Entry not found.')]
#[OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Access denied.')]
final readonly class UpdateEntryController
{
    public function __construct(
        private UpdateEntryHandler $handler,
        private Security $security,
    ) {}

    public function __invoke(string $id, Request $request): JsonResponse
    {
        if (!Uuid::isValid($id)) {
            return new JsonResponse(['error' => 'Invalid UUID.'], Response::HTTP_BAD_REQUEST);
        }

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
            ($this->handler)(new UpdateEntryCommand(
                $id,
                $user->id(),
                $title,
                $password,
                isset($data['login']) ? (string) $data['login'] : null,
                isset($data['url']) ? (string) $data['url'] : null,
                isset($data['notes']) ? (string) $data['notes'] : null,
            ));
        } catch (EntryNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (AccessDeniedException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        } catch (InvalidEntryTitleException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
