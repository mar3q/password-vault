<?php

declare(strict_types=1);

namespace App\Tests\Http\Identity;

use App\Tests\Http\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

final class ChangeEmailControllerTest extends ApiTestCase
{
    #[Test]
    public function it_changes_email(): void
    {
        $userId = $this->registerUser();
        $token = $this->getAuthToken();

        $this->client->jsonRequest('PATCH', '/api/users/' . $userId . '/email', [
            'email' => 'new@example.com',
        ], server: $this->authServer($token));

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    #[Test]
    public function it_returns_400_for_missing_email(): void
    {
        $userId = $this->registerUser();
        $token = $this->getAuthToken();

        $this->client->jsonRequest('PATCH', '/api/users/' . $userId . '/email', [], server: $this->authServer($token));

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    #[Test]
    public function it_returns_401_without_token(): void
    {
        $userId = $this->registerUser();

        $this->client->jsonRequest('PATCH', '/api/users/' . $userId . '/email', [
            'email' => 'new@example.com',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
