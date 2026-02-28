<?php

declare(strict_types=1);

namespace App\Tests\Http\Vault;

use App\Tests\Http\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

final class ListEntriesControllerTest extends ApiTestCase
{
    #[Test]
    public function it_returns_empty_list_for_new_user(): void
    {
        $this->registerUser();
        $token = $this->getAuthToken();

        $this->client->jsonRequest('GET', '/api/vault', server: $this->authServer($token));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame([], $this->responseJson());
    }

    #[Test]
    public function it_returns_only_own_entries(): void
    {
        $this->registerUser('user1@example.com', 'user1');
        $token1 = $this->getAuthToken('user1@example.com');

        $this->client->jsonRequest('POST', '/api/vault', [
            'title' => 'User1 Entry',
            'password' => 'secret',
        ], server: $this->authServer($token1));

        $this->registerUser('user2@example.com', 'user2');
        $token2 = $this->getAuthToken('user2@example.com');

        $this->client->jsonRequest('POST', '/api/vault', [
            'title' => 'User2 Entry',
            'password' => 'secret',
        ], server: $this->authServer($token2));

        $this->client->jsonRequest('GET', '/api/vault', server: $this->authServer($token1));

        $json = $this->responseJson();
        self::assertCount(1, $json);
        /** @var list<array{title: string}> $json */
        self::assertSame('User1 Entry', $json[0]['title']);
    }
}
