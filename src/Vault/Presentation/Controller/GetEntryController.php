<?php

declare(strict_types=1);

namespace App\Vault\Presentation\Controller;

use App\Identity\Infrastructure\Security\SecurityUser;
use App\Shared\Application\Port\QueryBus;
use App\Vault\Application\DTO\EntryDTO;
use App\Vault\Application\Query\GetEntryById\GetEntryByIdQuery;
use App\Vault\Domain\Exception\AccessDeniedException;
use App\Vault\Domain\Exception\EntryNotFoundException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/vault/{id}', methods: ['GET'])]
#[OA\Get(
    summary: 'Get a vault entry',
    description: 'Returns a single vault entry with decrypted password.',
    security: [['Bearer' => []]],
    tags: ['Vault'],
)]
#[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
#[OA\Response(response: Response::HTTP_OK, description: 'Entry found.')]
#[OA\Response(response: Response::HTTP_NOT_FOUND, description: 'Entry not found.')]
#[OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Access denied.')]
final readonly class GetEntryController
{
    public function __construct(
        private QueryBus $queryBus,
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
            /** @var EntryDTO $dto */
            $dto = $this->queryBus->ask(new GetEntryByIdQuery($id, $user->id()));
        } catch (EntryNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (AccessDeniedException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_FORBIDDEN);
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
        ]);
    }
}
