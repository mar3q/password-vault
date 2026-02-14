<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\EventDispatcher;

use App\Identity\Domain\Event\DomainEvent;
use App\Identity\Domain\Port\EventDispatcher;
use Psr\Log\LoggerInterface;

final readonly class SymfonyEventDispatcher implements EventDispatcher
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    /** @param DomainEvent[] $events */
    public function dispatch(array $events): void
    {
        foreach ($events as $event) {
            $this->logger->info('Domain event dispatched: {event}', [
                'event' => $event::class,
                'occurred_at' => $event->occurredAt()->format(\DateTimeInterface::ATOM),
            ]);
        }
    }
}
