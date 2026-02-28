<?php

declare(strict_types=1);

namespace App\Tests\Http\Vault;

use App\Tests\Http\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

final class DeleteEntryControllerTest extends ApiTestCase
{
    #[Test]
    public function it_deletes_an_entry(): void
    {
        $this->registerUser();
        $token = $this->getAuthToken();

        $this->client->jsonRequest('POST', '/api/vault', [
            'title' => 'ToDelete',
            'password' => 'secret',
        ], server: $this->authServer($token));

        $entryId = $this->responseJson()['id'];

        $this->client->jsonRequest('DELETE', '/api/vault/' . $entryId, server: $this->authServer($token));

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->client->jsonRequest('GET', '/api/vault/' . $entryId, server: $this->authServer($token));

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    public function it_returns_404_for_unknown_entry(): void
    {
        $this->registerUser();
        $token = $this->getAuthToken();

        $this->client->jsonRequest('DELETE', '/api/vault/00000000-0000-4000-8000-000000000000', server: $this->authServer($token));

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

        $this->registerUser('attacker@example.com', 'attacker');
        $attackerToken = $this->getAuthToken('attacker@example.com');

        $this->client->jsonRequest('DELETE', '/api/vault/' . $entryId, server: $this->authServer($attackerToken));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
