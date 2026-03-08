<?php

declare(strict_types=1);

namespace App\Shared\Application\Port;

interface QueryBus
{
    public function ask(object $query): mixed;
}
