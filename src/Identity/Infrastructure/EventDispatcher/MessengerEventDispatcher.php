<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\EventDispatcher;

use App\Identity\Domain\Event\DomainEvent;
use App\Identity\Domain\Port\EventDispatcher;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class MessengerEventDispatcher implements EventDispatcher
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
    ) {}

    /** @param DomainEvent[] $events */
    public function dispatch(array $events): void
    {
        foreach ($events as $event) {
            $this->logger->info('Dispatching domain event: {event}', [
                'event' => $event::class,
                'occurred_at' => $event->occurredAt()->format(\DateTimeInterface::ATOM),
            ]);

            $this->messageBus->dispatch($event);
        }
    }
}
