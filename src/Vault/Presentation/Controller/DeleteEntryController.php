<?php

declare(strict_types=1);

namespace App\Vault\Presentation\Controller;

use App\Identity\Infrastructure\Security\SecurityUser;
use App\Vault\Application\Command\DeleteEntry\DeleteEntryCommand;
use App\Vault\Application\Command\DeleteEntry\DeleteEntryHandler;
use App\Vault\Domain\Exception\AccessDeniedException;
use App\Vault\Domain\Exception\EntryNotFoundException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/vault/{id}', methods: ['DELETE'])]
#[OA\Delete(
    summary: 'Delete a vault entry',
    description: 'Permanently removes a vault entry.',
    security: [['Bearer' => []]],
    tags: ['Vault'],
)]
#[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
#[OA\Response(response: Response::HTTP_NO_CONTENT, description: 'Entry deleted.')]
#[OA\Response(response: Response::HTTP_NOT_FOUND, description: 'Entry not found.')]
#[OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Access denied.')]
final readonly class DeleteEntryController
{
    public function __construct(
        private DeleteEntryHandler $handler,
        private Security $security,
    ) {}

    public function __invoke(string $id): JsonResponse
    {
        if (!Uuid::isValid($id)) {
            return new JsonResponse(['error' => 'Invalid UUID.'], Response::HTTP_BAD_REQUEST);
        }

        /** @var SecurityUser $user */
        $user = $this->security->getUser();

        try {
            ($this->handler)(new DeleteEntryCommand($id, $user->id()));
        } catch (EntryNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (AccessDeniedException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
