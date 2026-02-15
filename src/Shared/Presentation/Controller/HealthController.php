<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Controller;

use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/health', methods: ['GET'])]
#[OA\Get(
    summary: 'Health check',
    description: 'Returns application health status.',
    tags: ['Health'],
)]
#[OA\Response(
    response: Response::HTTP_OK,
    description: 'Application is healthy.',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'status', type: 'string', example: 'ok'),
        ],
    ),
)]
final readonly class HealthController
{
    public function __invoke(LoggerInterface $logger): Response
    {
        $logger->info('checking application health');

        return new JsonResponse(['status' => 'ok']);
    }
}
