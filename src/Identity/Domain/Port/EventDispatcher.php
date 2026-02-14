<?php

declare(strict_types=1);

namespace App\Identity\Domain\Port;

use App\Identity\Domain\Event\DomainEvent;

interface EventDispatcher
{
    /** @param DomainEvent[] $events */
    public function dispatch(array $events): void;
}
