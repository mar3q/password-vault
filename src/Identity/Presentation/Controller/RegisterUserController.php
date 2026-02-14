<?php

declare(strict_types=1);

namespace App\Identity\Presentation\Controller;

use App\Identity\Application\Command\RegisterUser\RegisterUserCommand;
use App\Identity\Application\Command\RegisterUser\RegisterUserHandler;
use App\Identity\Domain\Exception\EmailAlreadyTakenException;
use App\Identity\Domain\Exception\InvalidEmailException;
use App\Identity\Domain\Exception\InvalidUsernameException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users', methods: ['POST'])]
final readonly class RegisterUserController
{
    public function __construct(
        private RegisterUserHandler $handler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        /** @var array{email?: string, username?: string, password?: string} $data */
        $data = json_decode($request->getContent(), true) ?: [];

        $email = $data['email'] ?? '';
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if ($email === '' || $username === '' || $password === '') {
            return new JsonResponse(
                ['error' => 'Fields "email", "username" and "password" are required.'],
                Response::HTTP_BAD_REQUEST,
            );
        }

        try {
            $userDTO = ($this->handler)(new RegisterUserCommand($email, $username, $password));
        } catch (InvalidEmailException|InvalidUsernameException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (EmailAlreadyTakenException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }

        return new JsonResponse([
            'id' => $userDTO->id,
            'email' => $userDTO->email,
            'username' => $userDTO->username,
        ], Response::HTTP_CREATED);
    }
}
