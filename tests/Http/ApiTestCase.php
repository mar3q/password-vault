<?php

declare(strict_types=1);

namespace App\Tests\Http;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->truncateTables();
    }

    /**
     * @return array<string, mixed>
     */
    protected function responseJson(): array
    {
        $content = $this->client->getResponse()->getContent();

        if ($content === false || $content === '') {
            return [];
        }

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }

    protected function registerUser(string $email = 'test@example.com', string $username = 'testuser', string $password = 'secret123'): string
    {
        $this->client->jsonRequest('POST', '/api/users', [
            'email' => $email,
            'username' => $username,
            'password' => $password,
        ]);

        return $this->responseJson()['id'];
    }

    protected function getAuthToken(string $email = 'test@example.com', string $password = 'secret123'): string
    {
        $this->client->jsonRequest('POST', '/api/auth/login', [
            'username' => $email,
            'password' => $password,
        ]);

        return $this->responseJson()['token'];
    }

    /**
     * @return array<string, string>
     */
    protected function authServer(string $token): array
    {
        return ['HTTP_AUTHORIZATION' => 'Bearer ' . $token];
    }

    private function truncateTables(): void
    {
        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $connection = $em->getConnection();

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        $connection->executeStatement('TRUNCATE TABLE vault_entries');
        $connection->executeStatement('TRUNCATE TABLE users');
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
