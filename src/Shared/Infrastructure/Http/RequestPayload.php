<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http;

use Symfony\Component\HttpFoundation\Request;

final class RequestPayload
{
    /** @return array<string, mixed> */
    public static function jsonDecode(Request $request): array
    {
        $content = $request->getContent();

        if ($content === '') {
            return [];
        }

        $data = json_decode($content, true);

        if (!is_array($data)) {
            throw new MalformedJsonException();
        }

        return $data;
    }
}
