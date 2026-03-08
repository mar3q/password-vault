<?php

declare(strict_types=1);

namespace App\Vault\Presentation\Controller;

use App\Identity\Infrastructure\Security\SecurityUser;
use App\Shared\Application\Port\QueryBus;
use App\Vault\Application\Query\ListEntries\ListEntriesQuery;
use OpenApi\Attributes as OA;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/vault', methods: ['GET'])]
#[OA\Get(
    summary: 'List vault entries',
    description: 'Returns all vault entries for the authenticated user.',
    security: [['Bearer' => []]],
    tags: ['Vault'],
)]
#[OA\Response(response: Response::HTTP_OK, description: 'List of entries.')]
final readonly class ListEntriesController
{
    public function __construct(
        private QueryBus $queryBus,
        private Security $security,
    ) {}

    public function __invoke(): JsonResponse
    {
        /** @var SecurityUser $user */
        $user = $this->security->getUser();

        /** @var list<\App\Vault\Application\DTO\EntryDTO> $entries */
        $entries = $this->queryBus->ask(new ListEntriesQuery($user->id()));

        return new JsonResponse(array_map(static fn($dto) => [
            'id' => $dto->id,
            'title' => $dto->title,
            'login' => $dto->login,
            'password' => $dto->password,
            'url' => $dto->url,
            'notes' => $dto->notes,
            'createdAt' => $dto->createdAt,
            'updatedAt' => $dto->updatedAt,
        ], $entries));
    }
}
