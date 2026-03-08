<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus;

use App\Shared\Application\Port\QueryBus;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerQueryBus implements QueryBus
{
    use HandleTrait;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function ask(object $query): mixed
    {
        try {
            return $this->handle($query);
        } catch (HandlerFailedException $e) {
            $exceptions = $e->getWrappedExceptions();

            if (\count($exceptions) === 1) {
                throw reset($exceptions);
            }

            throw $e;
        }
    }
}
