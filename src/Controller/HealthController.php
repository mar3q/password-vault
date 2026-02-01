<?php

declare(strict_types=1);

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/health', methods: ['GET'])]
final class HealthController
{
    public function __invoke(LoggerInterface $logger): Response
    {
        $logger->info('checking application health');
        return new JsonResponse(['status' => 'ok']);
    }
}
