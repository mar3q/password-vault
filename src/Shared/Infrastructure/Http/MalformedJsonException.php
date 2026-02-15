<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http;

final class MalformedJsonException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Malformed JSON in request body.');
    }
}
