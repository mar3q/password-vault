<?php

declare(strict_types=1);

namespace App\Tests\Http\Identity;

use App\Tests\Http\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

final class RegisterUserControllerTest extends ApiTestCase
{
    #[Test]
    public function it_registers_a_user(): void
    {
        $this->client->jsonRequest('POST', '/api/users', [
            'email' => 'new@example.com',
            'username' => 'newuser',
            'password' => 'secret123',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $json = $this->responseJson();
        self::assertSame('new@example.com', $json['email']);
        self::assertSame('newuser', $json['username']);
        self::assertArrayHasKey('id', $json);
    }

    #[Test]
    public function it_returns_400_for_missing_fields(): void
    {
        $this->client->jsonRequest('POST', '/api/users', ['email' => 'a@b.com']);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    #[Test]
    public function it_returns_409_for_duplicate_email(): void
    {
        $this->registerUser('dup@example.com', 'user1');

        $this->client->jsonRequest('POST', '/api/users', [
            'email' => 'dup@example.com',
            'username' => 'user2',
            'password' => 'secret123',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }

    #[Test]
    public function it_returns_400_for_invalid_email(): void
    {
        $this->client->jsonRequest('POST', '/api/users', [
            'email' => 'not-an-email',
            'username' => 'validuser',
            'password' => 'secret123',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }
}
