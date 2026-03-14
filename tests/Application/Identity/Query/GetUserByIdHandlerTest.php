<?php

declare(strict_types=1);

namespace App\Tests\Application\Identity\Query;

use App\Identity\Application\DTO\UserDTO;
use App\Identity\Application\Query\GetUserById\GetUserByIdHandler;
use App\Identity\Application\Query\GetUserById\GetUserByIdQuery;
use App\Identity\Domain\Exception\UserNotFoundException;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\HashedPassword;
use App\Identity\Domain\Model\User;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Model\Username;
use App\Identity\Domain\Port\UserRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GetUserByIdHandlerTest extends TestCase
{
    #[Test]
    public function it_returns_user_dto(): void
    {
        $userId = UserId::generate();
        $user = User::register(
            $userId,
            new Email('john@example.com'),
            new Username('john'),
            new HashedPassword('hashed'),
        );

        $repository = $this->createStub(UserRepository::class);
        $repository->method('findById')->willReturn($user);

        $handler = new GetUserByIdHandler($repository);

        $dto = $handler(new GetUserByIdQuery($userId->value()));

        self::assertInstanceOf(UserDTO::class, $dto);
        self::assertSame($userId->value(), $dto->id);
        self::assertSame('john@example.com', $dto->email);
        self::assertSame('john', $dto->username);
    }

    #[Test]
    public function it_throws_when_user_not_found(): void
    {
        $repository = $this->createStub(UserRepository::class);
        $repository->method('findById')->willReturn(null);

        $handler = new GetUserByIdHandler($repository);

        $this->expectException(UserNotFoundException::class);

        $handler(new GetUserByIdQuery(UserId::generate()->value()));
    }
}
