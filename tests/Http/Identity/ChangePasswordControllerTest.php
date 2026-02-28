<?php

declare(strict_types=1);

namespace App\Tests\Http\Identity;

use App\Tests\Http\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

final class ChangePasswordControllerTest extends ApiTestCase
{
    #[Test]
    public function it_changes_password(): void
    {
        $userId = $this->registerUser();
        $token = $this->getAuthToken();

        $this->client->jsonRequest('PATCH', '/api/users/' . $userId . '/password', [
            'password' => 'newsecret456',
        ], server: $this->authServer($token));

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    #[Test]
    public function it_returns_400_for_missing_password(): void
    {
        $userId = $this->registerUser();
        $token = $this->getAuthToken();

        $this->client->jsonRequest('PATCH', '/api/users/' . $userId . '/password', [], server: $this->authServer($token));

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }
}
