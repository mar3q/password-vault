<?php

declare(strict_types=1);

namespace App\Identity\Presentation\Controller;

use App\Identity\Application\Command\ChangePassword\ChangePasswordCommand;
use App\Identity\Application\Command\ChangePassword\ChangePasswordHandler;
use App\Identity\Domain\Exception\UserNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users/{id}/password', methods: ['PATCH'])]
final readonly class ChangePasswordController
{
    public function __construct(
        private ChangePasswordHandler $handler,
    ) {}

    public function __invoke(string $id, Request $request): JsonResponse
    {
        /** @var array{password?: string} $data */
        $data = json_decode($request->getContent(), true) ?: [];

        $password = $data['password'] ?? '';

        if ($password === '') {
            return new JsonResponse(
                ['error' => 'Field "password" is required.'],
                Response::HTTP_BAD_REQUEST,
            );
        }

        try {
            ($this->handler)(new ChangePasswordCommand($id, $password));
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (UserNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
