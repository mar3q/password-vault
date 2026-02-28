<?php

declare(strict_types=1);

namespace App\Tests\Http\Identity;

use App\Tests\Http\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

final class GetUserControllerTest extends ApiTestCase
{
    #[Test]
    public function it_returns_user_by_id(): void
    {
        $userId = $this->registerUser();
        $token = $this->getAuthToken();

        $this->client->jsonRequest('GET', '/api/users/' . $userId, server: $this->authServer($token));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $json = $this->responseJson();
        self::assertSame('test@example.com', $json['email']);
    }

    #[Test]
    public function it_returns_404_for_unknown_user(): void
    {
        $this->registerUser();
        $token = $this->getAuthToken();

        $this->client->jsonRequest('GET', '/api/users/00000000-0000-4000-8000-000000000000', server: $this->authServer($token));

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    public function it_returns_401_without_token(): void
    {
        $userId = $this->registerUser();

        $this->client->jsonRequest('GET', '/api/users/' . $userId);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
