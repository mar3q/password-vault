<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus;

use App\Shared\Application\Port\CommandBus;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerCommandBus implements CommandBus
{
    use HandleTrait;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function dispatch(object $command): mixed
    {
        try {
            return $this->handle($command);
        } catch (HandlerFailedException $e) {
            $exceptions = $e->getWrappedExceptions();

            if (\count($exceptions) === 1) {
                throw reset($exceptions);
            }

            throw $e;
        }
    }
}
