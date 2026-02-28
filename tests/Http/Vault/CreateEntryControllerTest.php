<?php

declare(strict_types=1);

namespace App\Tests\Http\Vault;

use App\Tests\Http\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

final class CreateEntryControllerTest extends ApiTestCase
{
    #[Test]
    public function it_creates_a_vault_entry(): void
    {
        $this->registerUser();
        $token = $this->getAuthToken();

        $this->client->jsonRequest('POST', '/api/vault', [
            'title' => 'GitHub',
            'password' => 'my-secret',
            'login' => 'john@example.com',
            'url' => 'https://github.com',
            'notes' => 'Work account',
        ], server: $this->authServer($token));

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $json = $this->responseJson();
        self::assertSame('GitHub', $json['title']);
        self::assertSame('my-secret', $json['password']);
        self::assertSame('john@example.com', $json['login']);
        self::assertArrayHasKey('id', $json);
    }

    #[Test]
    public function it_creates_entry_with_minimal_fields(): void
    {
        $this->registerUser();
        $token = $this->getAuthToken();

        $this->client->jsonRequest('POST', '/api/vault', [
            'title' => 'Minimal',
            'password' => 'secret',
        ], server: $this->authServer($token));

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $json = $this->responseJson();
        self::assertNull($json['login']);
        self::assertNull($json['url']);
        self::assertNull($json['notes']);
    }

    #[Test]
    public function it_returns_400_for_missing_fields(): void
    {
        $this->registerUser();
        $token = $this->getAuthToken();

        $this->client->jsonRequest('POST', '/api/vault', [
            'title' => 'NoPassword',
        ], server: $this->authServer($token));

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    #[Test]
    public function it_returns_401_without_token(): void
    {
        $this->client->jsonRequest('POST', '/api/vault', [
            'title' => 'GitHub',
            'password' => 'secret',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
