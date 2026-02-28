<?php

declare(strict_types=1);

namespace App\Tests\Http\Vault;

use App\Tests\Http\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

final class GetEntryControllerTest extends ApiTestCase
{
    #[Test]
    public function it_returns_entry_with_decrypted_password(): void
    {
        $this->registerUser();
        $token = $this->getAuthToken();

        $this->client->jsonRequest('POST', '/api/vault', [
            'title' => 'GitHub',
            'password' => 'my-secret',
        ], server: $this->authServer($token));

        $entryId = $this->responseJson()['id'];

        $this->client->jsonRequest('GET', '/api/vault/' . $entryId, server: $this->authServer($token));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $json = $this->responseJson();
        self::assertSame('GitHub', $json['title']);
        self::assertSame('my-secret', $json['password']);
    }

    #[Test]
    public function it_returns_404_for_unknown_entry(): void
    {
        $this->registerUser();
        $token = $this->getAuthToken();

        $this->client->jsonRequest('GET', '/api/vault/00000000-0000-4000-8000-000000000000', server: $this->authServer($token));

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    public function it_returns_403_for_other_users_entry(): void
    {
        $this->registerUser('owner@example.com', 'owner');
        $ownerToken = $this->getAuthToken('owner@example.com');

        $this->client->jsonRequest('POST', '/api/vault', [
            'title' => 'Private',
            'password' => 'secret',
        ], server: $this->authServer($ownerToken));

        $entryId = $this->responseJson()['id'];

        $this->registerUser('other@example.com', 'other');
        $otherToken = $this->getAuthToken('other@example.com');

        $this->client->jsonRequest('GET', '/api/vault/' . $entryId, server: $this->authServer($otherToken));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
