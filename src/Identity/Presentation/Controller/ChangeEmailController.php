<?php

declare(strict_types=1);

namespace App\Identity\Presentation\Controller;

use App\Identity\Application\Command\ChangeEmail\ChangeEmailCommand;
use App\Identity\Application\Command\ChangeEmail\ChangeEmailHandler;
use App\Identity\Domain\Exception\EmailAlreadyTakenException;
use App\Identity\Domain\Exception\InvalidEmailException;
use App\Identity\Domain\Exception\UserNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users/{id}/email', methods: ['PATCH'])]
final readonly class ChangeEmailController
{
    public function __construct(
        private ChangeEmailHandler $handler,
    ) {}

    public function __invoke(string $id, Request $request): JsonResponse
    {
        /** @var array{email?: string} $data */
        $data = json_decode($request->getContent(), true) ?: [];

        $email = $data['email'] ?? '';

        if ($email === '') {
            return new JsonResponse(
                ['error' => 'Field "email" is required.'],
                Response::HTTP_BAD_REQUEST,
            );
        }

        try {
            ($this->handler)(new ChangeEmailCommand($id, $email));
        } catch (\InvalidArgumentException|InvalidEmailException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (UserNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (EmailAlreadyTakenException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
