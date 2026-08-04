<?php

namespace App\Services\Scanner\DTO;

final class PageContent
{
    public function __construct(
        public readonly string $url,
        public readonly int $statusCode,
        public readonly array $headers,
        public readonly string $html,
        public readonly int $responseTimeMs,
        public readonly array $redirectChain = [],
    ) {}
}
