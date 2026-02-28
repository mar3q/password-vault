<?php

declare(strict_types=1);

namespace App\Tests\Http\Auth;

use App\Tests\Http\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

final class LoginControllerTest extends ApiTestCase
{
    #[Test]
    public function it_returns_jwt_token(): void
    {
        $this->registerUser('login@example.com', 'loginuser', 'secret123');

        $this->client->jsonRequest('POST', '/api/auth/login', [
            'username' => 'login@example.com',
            'password' => 'secret123',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $json = $this->responseJson();
        self::assertArrayHasKey('token', $json);
        self::assertNotEmpty($json['token']);
    }

    #[Test]
    public function it_returns_401_for_wrong_password(): void
    {
        $this->registerUser('login@example.com', 'loginuser', 'secret123');

        $this->client->jsonRequest('POST', '/api/auth/login', [
            'username' => 'login@example.com',
            'password' => 'wrong-password',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    public function it_returns_401_for_unknown_user(): void
    {
        $this->client->jsonRequest('POST', '/api/auth/login', [
            'username' => 'nobody@example.com',
            'password' => 'secret123',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
